<?php

namespace Bitrix\Main\Access\Auth;

use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type\MemberEntityType;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Event;
use Bitrix\Main\UserAccessTable;

class HrAccessEventHandler
{
	public static function onMemberUpdated(Event $event): void
	{
		$updatedFields = $event->getParameter('fields');
		$member = $event->getParameter('previousMember');

		if (!$member || $member->entityType !== MemberEntityType::USER)
		{
			return;
		}

		$headRole = Container::getRoleHelperService()->getHeadRoleId();
		$deputyRole = Container::getRoleHelperService()->getDeputyRoleId();
		$role = $member->roles[0];

		if (in_array('role', $updatedFields, true))
		{
			(new AccessAuthProvider())->DeleteByUser($member->entityId);
		}

		if (!in_array($role, [$headRole, $deputyRole], true))
		{
			return;
		}

		$type = $role === $headRole ? AccessCode::ACCESS_DIRECTOR : AccessCode::ACCESS_DEPUTY;
		$accessCode = $type . $member->nodeId;

		// some collision between old and new node id
		self::deleteByAccessCode($accessCode);
	}

	public static function onMemberDeleted(Event $event): void
	{
		self::onMemberUpdated($event);
	}

	public static function onMemberAdded(Event $event): void
	{
		$member = $event->getParameter('member');

		if (!$member || $member->entityType !== MemberEntityType::USER)
		{
			return;
		}

		(new AccessAuthProvider())->DeleteByUser($member->entityId);
	}

	private static function deleteByAccessCode(string $accessCode): void
	{
		// find users by access codes
		$res = UserAccessTable::getList(
			[
				'filter' => [
					'=ACCESS_CODE' => $accessCode,
				],
				'select' => ['USER_ID'],
			],
		);

		$provider = new AccessAuthProvider();
		while ($row = $res->fetch())
		{
			$provider->DeleteByUser($row['USER_ID']);
		}
	}
}