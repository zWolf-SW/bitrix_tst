<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Bizproc\WorkflowInstanceTable;
use Bitrix\Main\EO_User;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class WorkflowUserView implements \JsonSerializable
{
	protected WorkflowState $workflow;
	private array $tasks;
	private array $myRunningTasks;
	private array $myCompletedTasks;
	protected int $userId;

	protected ?WorkflowFacesView $faces = null;
	protected ?bool $isCompleted = null;

	public function __construct(WorkflowState $workflow, int $userId)
	{
		$this->workflow = $workflow;
		$this->userId = $userId;

		$this->tasks = \CBPViewHelper::getWorkflowTasks($workflow['ID'], true, true);
		$this->myRunningTasks = $this->getMyWaitingTasks();
		$this->myCompletedTasks = $this->getMyCompletedTasks();
	}

	public function toArray(): array
	{
		return $this->jsonSerialize();
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'data' => [
				'id' => $this->getId(),
				'typeName' => $this->getTypeName(),
				'itemName' => $this->getName(),
				'itemDescription' => $this->getDescription(),
				'itemTime' => $this->getTime(),
				'statusText' => $this->getStatusText(),
				'faces' => $this->getFaces(),
				'tasks' => $this->getTasks(),
				'authorId' => $this->getAuthorId(),
				'newCommentsCounter' => $this->getCommentCounter(),
				'result' => $this->getIsCompleted() ? $this->getWorkflowResult() : null,
			],
		];
	}

	public function getId(): string
	{
		return $this->workflow->getId();
	}

	public function getName(): mixed
	{
		if ($this->getTasks())
		{
			return current($this->getTasks())['name'];
		}

		if (!$this->isWorkflowAuthorView())
		{
			$task = current($this->getCompletedTasks());
			if ($task)
			{
				return $task['name'];
			}
		}

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		return html_entity_decode(
			$documentService->getDocumentName($this->workflow->getComplexDocumentId()) ?? ''
		);
	}

	public function getDescription(): ?string
	{
		$task = false;
		if ($this->getTasks())
		{
			$task = current($this->getTasks());
		}
		elseif (!$this->isWorkflowAuthorView() && $this->getCompletedTasks())
		{
			$task = current($this->getCompletedTasks());
		}

		if ($task)
		{
			return trim($task['description']);
		}

		return null;
	}

	public function getStatusText(): mixed
	{
		return $this->workflow->getStateTitle();
	}

	public function getIsCompleted(): bool
	{
		if ($this->isCompleted === null)
		{
			$this->isCompleted = !WorkflowInstanceTable::exists($this->getId());
		}

		return $this->isCompleted;
	}

	public function getCommentCounter(): int
	{
		$row = WorkflowUserCommentTable::getList([
			'filter' => [
				'=WORKFLOW_ID' => $this->getId(),
				'=USER_ID' => $this->userId,
			],
			'select' => ['UNREAD_CNT'],
		])->fetch();

		return $row ? (int)$row['UNREAD_CNT'] : 0;
	}

	public function getTasks(): array
	{
		return $this->myRunningTasks;
	}

	public function getTaskById(int $taskId): ?array
	{
		foreach ($this->getTasks() as $task)
		{
			if ($task['id'] === $taskId)
			{
				return $task;
			}
		}

		foreach ($this->getCompletedTasks() as $task)
		{
			if ($task['id'] === $taskId)
			{
				return $task;
			}
		}

		$task = \CBPTaskService::getList(
			[],
			['ID' => $taskId, 'USER_ID' => $this->userId],
			false,
			false,
			[
				'ID',
				'MODIFIED',
				'NAME',
				'DESCRIPTION',
				'PARAMETERS',
				'STATUS',
				'IS_INLINE',
				'ACTIVITY',
				'ACTIVITY_NAME',
				'CREATED_DATE',
				'DELEGATION_TYPE',
				'OVERDUE_DATE',
			],
		)->getNext();

		if ($task)
		{
			$task['USERS'] = \CBPTaskService::getTaskUsers($taskId)[$taskId] ?? [];
			$preparedTasks = $this->prepareTasks([$task]);

			return $preparedTasks[0] ?? null;
		}

		return null;
	}

	public function getCompletedTasks(): array
	{
		return $this->myCompletedTasks;
	}

	public function getAuthorId(): mixed
	{
		return $this->workflow->getStartedBy();
	}

	protected function getTime(): string
	{
		return \CBPViewHelper::formatDateTime($this->workflow->getModified());
	}

	public function getOverdueDate(): ?DateTime
	{
		$task = $this->getTasks()[0] ?? null;
		if (!$task || !isset($task['overdueDate']))
		{
			return null;
		}

		return DateTime::createFromUserTime($task['overdueDate']);
	}

	public function getStartedBy(): ?EO_User
	{
		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();
		$currentUserId = $currentUser->getId();
		if (!is_null($currentUserId) && (int)$currentUserId === $this->workflow->getStartedBy())
		{
			$rows = [
				'ID' => $currentUser->getId(),
				'LOGIN' => $currentUser->getLogin(),
				'NAME' => $currentUser->getFirstName(),
				'SECOND_NAME' => $currentUser->getSecondName(),
				'LAST_NAME' => $currentUser->getLastName(),
			];

			return UserTable::wakeUpObject($rows);
		}

		return \Bitrix\Main\UserTable::getByPrimary(
			$this->workflow->getStartedBy(),
			['select' => ['ID', 'LOGIN', 'NAME', 'SECOND_NAME', 'LAST_NAME']]
		)->fetchObject();
	}

	public function getFaces(): WorkflowFacesView
	{
		if (!$this->faces)
		{
			$task = $this->getFirstRunningTask();
			$this->faces = new WorkflowFacesView($this->getId(), $task ? $task['id'] : null);
		}

		return $this->faces;
	}

	private function getMyWaitingTasks(): array
	{
		$userId = $this->userId;

		$myTasks = array_filter(
			$this->tasks['RUNNING'],
			static function($task) use ($userId) {
				$waitingUsers = array_filter(
					$task['USERS'],
					static fn ($user) => ((int)$user['STATUS'] === \CBPTaskUserStatus::Waiting),
				);

				return in_array($userId, array_column($waitingUsers, 'USER_ID'));
			},
		);

		return $this->prepareTasks(array_values($myTasks));
	}

	private function getMyCompletedTasks(): array
	{
		$userId = $this->userId;

		$completedRunningTasks = array_filter(
			$this->tasks['RUNNING'],
			static function ($task) use ($userId) {
				$completedUsers = array_filter(
					$task['USERS'],
					static fn ($user) => ((int)$user['STATUS'] !== \CBPTaskUserStatus::Waiting),
				);

				$taskUserIds = array_column($completedUsers, 'USER_ID');
				Collection::normalizeArrayValuesByInt($taskUserIds);

				return in_array($userId, $taskUserIds, true);
			}
		);

		$completedTasks = array_filter(
			$this->tasks['COMPLETED'],
			static function ($task) use ($userId) {
				$taskUserIds = array_column($task['USERS'], 'USER_ID');
				Collection::normalizeArrayValuesByInt($taskUserIds);

				return in_array($userId, $taskUserIds, true);
			}
		);

		return $this->prepareTasks(array_values(array_merge($completedRunningTasks, $completedTasks)));
	}

	protected function prepareTasks(array $myTasks): array
	{
		$isRpa = $this->workflow->getModuleId() === 'rpa';
		$userId = $this->userId;

		$tasks = [];
		foreach ($myTasks as $task)
		{
			$isRunning = (int)$task['STATUS'] === \CBPTaskStatus::Running;
			if ($isRunning)
			{
				$users = array_filter(
					$task['USERS'] ?? [],
					static function ($user) use ($userId) {
						return (int)$user['USER_ID'] === $userId;
					},
				);
				if ($users)
				{
					$user = current($users);
					$isRunning = (int)$user['STATUS'] === \CBPTaskUserStatus::Waiting;
				}
			}

			$controls = $isRunning ? \CBPDocument::getTaskControls($task, $userId) : [];
			$buttons = $controls['BUTTONS'] ?? null;
			if (!empty($buttons))
			{
				foreach ($buttons as &$button)
				{
					if (!empty($button['TEXT']))
					{
						$button['TEXT'] = html_entity_decode(htmlspecialcharsback($button['TEXT']));
					}
				}

				unset($button);
			}

			$taskId = (int)$task['ID'];
			$tasks[] = [
				'id' => $taskId,
				'name' => html_entity_decode($task['~NAME']),
				'description' => $task['~DESCRIPTION'],
				'isInline' => \CBPHelper::getBool($task['IS_INLINE']),
				'controls' => [
					'buttons' => $buttons,
					'fields' => $controls['FIELDS'] ?? null,
				],
				'createdDate' => $task['~CREATED_DATE'] ?? null,
				'delegationType' => $task['~DELEGATION_TYPE'] ?? null,
				'overdueDate' => $task['~OVERDUE_DATE'] ?? null,
				'url' => $isRpa
					? "/rpa/task/id/$taskId/"
					: sprintf(
						'/company/personal/bizproc/%s/?USER_ID=%d',
						$taskId,
						$this->userId
					)
				,
				'userId' => $userId,
				'isRunning' => $isRunning,
			];
		}

		return $tasks;
	}

	public function getTypeName(): mixed
	{
		$this->workflow->fillTemplate();
		if (
			$this->workflow->getModuleId() !== 'lists'
			&& !empty($this->workflow->getTemplate()?->getName())
		)
		{
			return $this->workflow->getTemplate()?->getName();
		}

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		$complexDocumentType = null;
		try
		{
			$complexDocumentType = $documentService->getDocumentType($this->workflow->getComplexDocumentId());
		}
		catch (SystemException | \Exception $exception)
		{}

		return $complexDocumentType ? $documentService->getDocumentTypeCaption($complexDocumentType) : null;
	}

	public function getWorkflowResult(): ?array
	{
		return \CBPViewHelper::getWorkflowResult($this->getId(), $this->userId);
	}

	protected function isWorkflowAuthorView(): bool
	{
		return $this->getAuthorId() === $this->userId;
	}

	private function formatDateTime(string $format, $datetime): ?string
	{
		if ($datetime instanceof DateTime)
		{
			$datetime = (string)$datetime;
		}

		if (is_string($datetime) && DateTime::isCorrect($datetime))
		{
			$timestamp = (new DateTime($datetime))->getTimestamp();

			return FormatDate($format, $timestamp);
		}

		return null;
	}

	protected function getAuthorView(): ?UserView
	{
		return \Bitrix\Bizproc\UI\UserView::createFromId($this->getAuthorId());
	}

	protected function getDocumentUrl(): ?string
	{
		$complexDocumentId = $this->workflow->getComplexDocumentId();

		return \CBPDocument::getDocumentAdminPage($complexDocumentId);
	}

	public function getFirstRunningTask(): ?array
	{
		return $this->getTasks()[0] ?? null;
	}

	protected function isRunningTaskUser(array $task): bool
	{
		$taskUsers = $task['USERS'] ?? [];

		foreach ($taskUsers as $user)
		{
			if ((int)$user['USER_ID'] === $this->userId)
			{
				return (int)$user['STATUS'] === \CBPTaskUserStatus::Waiting;
			}
		}

		return false;
	}

	protected function getTaskControls(array $task): array
	{
		$controls = \CBPDocument::getTaskControls($task, $this->userId);
		$buttons = $controls['BUTTONS'] ?? null;
		if (!empty($buttons))
		{
			foreach ($buttons as &$button)
			{
				if (!empty($button['TEXT']))
				{
					$button['TEXT'] = html_entity_decode(htmlspecialcharsback($button['TEXT']));
				}
			}

			unset($button);
		}

		return [
			'buttons' => $buttons,
			'fields' => $controls['FIELDS'] ?? null,
		];
	}
}
