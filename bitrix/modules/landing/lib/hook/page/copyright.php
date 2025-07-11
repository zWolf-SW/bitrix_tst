<?php

namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Hook;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Field;
use Bitrix\Landing\Internals\HookDataTable;
use Bitrix\Landing\Restriction\Site;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\UI\Extension;

class Copyright extends \Bitrix\Landing\Hook\Page
{
	protected const PAGE_TYPE_PAGE = 'PAGE';
	protected const PAGE_TYPE_STORE = 'STORE';

	protected const REGIONS_RU_LANG = ['ru', 'by', 'kz'];

	private $lang;
	private $siteId;

	/**
	 * Actual only for ru/by/kz, for any languages always 1
	 */
	protected const RANDOM_PHRASE_COUNT = 32;

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return [
			'SHOW' => new Field\Checkbox('SHOW', [
				'title' => Manager::isB24()
					? Loc::getMessage('LANDING_HOOK_COPYRIGHT_SHOW')
					: Loc::getMessage('LANDING_HOOK_COPYRIGHT_SHOW_SMN'),
			]),
			'CODE' => new Field\Text('CODE', [
				'title' => Loc::getMessage('LANDING_HOOK_COPYRIGHT_CODE'),
			]),
		];
	}

	/**
	 * Enable only in high plan.
	 * @return boolean
	 */
	public function isFree()
	{
		return false;
	}

	/**
	 * Locked or not current hook in free plan.
	 * @return bool
	 */
	public function isLocked()
	{
		return !\Bitrix\Landing\Restriction\Manager::isAllowed(
			'limit_sites_powered_by'
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		if ($this->isLocked())
		{
			return true;
		}

		return $this->fields['SHOW']->getValue() !== 'N';
	}

	/**
	 * Exec hook. Now do nothing, because using print in template
	 * @return void
	 */
	public function exec()
	{
		if ($this->execCustom())
		{
			return;
		}

		$this->setLang(Manager::getZone());
	}

	/**
	 * Save current site language
	 * @param string|null $lang Language.
	 * @return void
	 */
	public function setLang(?string $lang): void
	{
		$this->lang = $this->lang ?: $lang;
	}

	/**
	 * Get current site language
	 * @return string
	 */
	protected function getLang(): string
	{
		return $this->lang ?: Manager::getZone();
	}

	/**
	 * Save current site id
	 * @param int|null $siteId SiteId.
	 * @return void
	 */
	public function setSiteId(?int $siteId): void
	{
		$this->siteId = $this->siteId ?: $siteId;
	}

	/**
	 * Get current site id
	 * @return int
	 */
	protected function getSiteId(): string
	{
		return $this->siteId ?: 0;
	}

	/**
	 * Check is current site lang in ru region
	 * @return bool
	 */
	protected function isRuLang(): bool
	{
		return in_array($this->getLang(), self::REGIONS_RU_LANG, true);
	}

	/**
	 * Return footer html
	 * @return string
	 */
	public function view(): string
	{
		$isTermsFooterShow = Site::isTermsFooterShow();
		$isHookEnabled = $this->enabled();
		if ($isTermsFooterShow || $isHookEnabled)
		{
			$footer = '<div class="bitrix-footer">';
			$footer .= '<span class="bitrix-footer-text">';
			if ($isHookEnabled)
			{
				$footer .= '<div class="bitrix-footer-seo">';
				$footer .= $this->getCommonText();
				$footer .= $this->getAdditionalText();
				$footer .= '</div>';
				$footer .= $this->getTermsContent();
			}
			if ($isTermsFooterShow && !$isHookEnabled)
			{
				$footer .= $this->getTermsContent();
			}
			$footer .= '</span>';
			$footer .= '</div>';

			Extension::load('ui.hint');
			$footer .= "<script>BX.ready(function() {BX.UI.Hint.init(BX('.bitrix-footer-terms'))})</script>";

			return $footer;
		}

		return '';
	}

	protected function getCommonText(): ?string
	{
		$isB24 = Manager::isB24();
		$lang = $this->getLang();

		$commonText = Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_COMMON', null, $lang);
		$logoUrl = Manager::getUrlFromFile(
			'/bitrix/images/landing/copyright/logo_'
			. (in_array($lang, ['ru', 'ua', 'en']) ? $lang : 'en')
			. '.svg?1'
		);
		$logoAlt = Loc::getMessage('LANDING_HOOK_COPYRIGHT_LOGO_ALT', null, $lang);
		$logo = '<img src="' . $logoUrl . '" alt="' . $logoAlt . '">';

		// RU
		if ($isB24 && $this->isRuLang())
		{
			return str_replace(
				[
					'#LOGO#',
					'<linklogo>', '</linklogo>',
					'<linksite>', '</linksite>',
					'<linkcrm>', '</linkcrm>',
				],
				[
					$logo, '', '', '', '', '', '',
				],
				$commonText
			);
		}

		// SMN
		$component = $this->getPublicComponent();
		if (!$isB24)
		{
			$advCode = $component->getAdvCode();

			return
				Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_SMN_1')
				. '<a href="https://www.1c-bitrix.ru/?' . $advCode . '" target="_blank" class="bitrix-footer-link">'
				. Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_SMN_2')
				. '</a>'
			;
		}

		// Not RU and B24
		$linkSite = $component->getRefLink('websites', true, true);
		if ($linkSite)
		{
			return Loc::getMessage(
				'LANDING_HOOK_COPYRIGHT_TEXT_COMMON_EN',
				[
					'#LOGO#' => $logo,
					'<linksite>' => '<a class="bitrix-footer-link" target="_blank" href="' . $linkSite . '">',
					'</linksite>' => '</a>',
				],
				$lang
			);
		}

		// default
		return str_replace(
			[
				'#LOGO#',
				'<linklogo>', '</linklogo>',
				'<linksite>', '</linksite>',
				'<linkcrm>', '</linkcrm>',
			],
			[
				$logo, '', '', '', '', '', '',
			],
			$commonText
		);
	}

	/**
	 * Return random additional phrase with link
	 * @return string
	 */
	protected function getAdditionalText(): string
	{
		if (!$this->isRuLang() || !Manager::isB24())
		{
			return '';
		}

		$type = strtoupper(Landing::getSiteType());
		$phrases = $this->getRandomPhraseCollection($type);
		$code = (int)$this->fields['CODE']->getValue() ?: 1;
		$text = $phrases[$code] ?: $phrases[1];
		if (is_array($text))
		{
			$text = $text[0];
		}
		$component = $this->getPublicComponent();

		$link = $component->getRefLink('websites', true, true);
		if ($type === self::PAGE_TYPE_STORE)
		{
			$link = str_replace('features/sites.php', 'features/shop.php', $link);
		}

		return '. <a href="' . $link . '" class="bitrix-footer-link">' . $text . '</a>';
	}

	protected function getPublicComponent(): \LandingPubComponent
	{
		/**
		 * @var $component ?\LandingPubComponent
		 */
		static $component = null;

		if (!$component)
		{
			$componentClass = \CBitrixComponent::includeComponentClass('bitrix:landing.pub');
			$component = new $componentClass;
			$component->setZone($this->getLang());
		}

		return $component;
	}

	protected function getTermsContent(): string
	{
		$reportUrl = $this->getReportUrl();
		$reportLinkOpen =
			'<a class="bitrix-footer-link" target="_blank" rel="nofollow" href="'
			. $reportUrl->getUri()
			. '">'
		;
		$reportText = Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_CONTENT_LINK_REPORT_2', [
			'#a1#' => $reportLinkOpen,
			'#a2#' => '</a>',
		]);

		$lang = $this->getLang();
		$hintText = Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_CONTENT_LINK_REPORT_HINT', null, $lang);

		return <<<HTML
			<div class="bitrix-footer-terms">
		        <span class="bitrix-footer-link bitrix-footer-link-report">$reportText</span>
				<span class="bitrix-footer-hint" data-hint="$hintText"></span>
			</div>
		HTML;
	}

	private function getReportUrl(): Uri
	{
		$links = [
			'en' => [
				'report' => 'https://www.bitrix24.com/abuse/',
			],
			'ru' => [
				'report' => 'https://www.bitrix24.ru/abuse/',
			],
			'br' => [
				'report' => 'https://www.bitrix24.com.br/abuse/',
			],
			'de' => [
				'report' => 'https://www.bitrix24.de/abuse/',
			],
			'la' => [
				'report' => 'https://www.bitrix24.es/abuse/',
			],
			'ua' => [
				'report' => 'https://www.bitrix24.ua/abuse/',
			],
			'kz' => [
				'report' => 'https://www.bitrix24.kz/abuse/',
			],
			'by' => [
				'report' => 'https://www.bitrix24.by/abuse/',
			],
		];

		$lang = $this->getLang();
		$reportUrl = new Uri($links[$lang]['report'] ?? $links['com']['report']);

		$protocol = Manager::isHttps() ? 'https://' : 'http://';
		$fromUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$portalName = \COption::getOptionString('main', 'server_name', '');
		$senderPage = strtoupper(Landing::getSiteType());

		$reportUrl->addParams([
			'sender_page' => urlencode($senderPage),
			'hostname' => urlencode($portalName),
			'siteId' => urlencode($this->getSiteId()),
			'from_url' => urlencode($fromUrl),
		]);

		return $reportUrl;
	}

	/**
	 * Collection of random phrases
	 * @return array|array[]
	 */
	protected function getRandomPhraseCollection(string $type): array
	{
		$phrases = [];

		if ($this->isRuLang())
		{
			Loc::loadMessages(Manager::getDocRoot() . '/bitrix/modules/landing/lib/hook/page/copyright_notranslate.php');
			if ($type === self::PAGE_TYPE_STORE)
			{
				for ($i = 1; $i <= self::RANDOM_PHRASE_COUNT; $i++)
				{
					$phrases[$i] = Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_STORE_' . $i, null, 'ru');
				}
			}
			// default
			else
			{
				for ($i = 1; $i <= self::RANDOM_PHRASE_COUNT; $i++)
				{
					$phrases[$i] = Loc::getMessage('LANDING_HOOK_COPYRIGHT_TEXT_PAGE_' . $i, null, 'ru');
				}
			}
		}

		return $phrases;
	}

	/**
	 * Get random text code from collection
	 * @return int
	 */
	public static function getRandomPhraseId(): int
	{
		return rand(1, self::RANDOM_PHRASE_COUNT);
	}

	/**
	 * Hook copy handler for save editor value, randomizer
	 * @param array|null $data Data.
	 * @param int $entityId Entity.
	 * @param string $type Type.
	 * @param bool $publication Is publication.
	 *
	 * @return array|null
	 */
	public static function onCopy(?array $data, int $entityId, string $type, bool $publication = false): ?array
	{
		// only for site
		if ($type !== Hook::ENTITY_TYPE_SITE)
		{
			return $data;
		}

		$data = $data ?: [];
		$newData = $data;

		if (!isset($newData['SHOW']))
		{
			$newData['SHOW'] = 'Y';
		}
		if (
			!isset($newData['CODE'])
			|| !$publication
		)
		{
			$newData['CODE'] = self::getRandomPhraseId();
		}

		// update
		if (
			$data !== $newData
			&& $publication
		)
		{
			$fields = [
				'HOOK' => 'COPYRIGHT',
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE' => $type,
				'PUBLIC' => 'N',
			];
			$existing = HookDataTable::getList([
				'select' => ['ID', 'CODE'],
				'filter' => $fields,
			]);
			while ($row = $existing->fetch())
			{
				$res = HookDataTable::update($row['ID'], [
					'VALUE' => $newData[$row['CODE']],
				]);
				if ($res->isSuccess())
				{
					unset($newData[$row['CODE']]);
				}
			}

			if (!empty($newData))
			{
				foreach($newData as $code => $value)
				{
					$fieldsAdd = $fields;
					$fieldsAdd['CODE'] = $code;
					$fieldsAdd['VALUE'] = $value;
					HookDataTable::add($fieldsAdd);
				}
			}
		}

		return $newData;
	}
}
