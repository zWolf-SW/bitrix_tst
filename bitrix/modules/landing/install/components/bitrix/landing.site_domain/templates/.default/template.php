<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Restriction;
use Bitrix\Landing\Domain\Register;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Landing\Manager;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var \LandingSiteDomainComponent $component */
/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

if ($this->getComponent()->request('save') == 'Y' && !$arResult['ERRORS'])
{
	?>
	<script>
		if (typeof top.BX.SidePanel !== 'undefined')
		{
			setTimeout(function() {
				top.BX.SidePanel.Instance.close();
				top.BX.onCustomEvent('BX.Landing.Filter:apply');
			}, 300);
		}
	</script>
	<?
}

// load
Loc::loadMessages(__FILE__);
Extension::load([
	'ui.common', 'ui.alerts',
	'ui.forms', 'ui.buttons',
	'ui.dialogs.messagebox',
	'ui.info-helper', 'ui.hint'
]);
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));
Toolbar::deleteFavoriteStar();

// errors
if ($arResult['ERRORS'])
{
	?><div class="ui-alert ui-alert-danger" id="domain-error-alert"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?></div><?
}
if ($arResult['FATAL'])
{
	return;
}

// vars
$tab = $this->getComponent()->request('tab');

// uri
$uriSave = new \Bitrix\Main\Web\Uri(
	\htmlspecialcharsback(POST_FORM_ACTION_URI)
);
$uriSave->addParams([
	'save' => 'Y'
]);

// left panel
$menuItems = [
	'provider' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_FREE'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'provider'], ['save']),
			'data-slider-ignore-autobinding' => 'true'
		],
		'HELP_CODE' => 'DOMAIN_FREE'
	],
	'bitrix24' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_BITRIX24'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'bitrix24'], ['save']),
			'data-slider-ignore-autobinding' => 'true'
		],
		'HELP_CODE' => 'DOMAIN_BITRIX24'
	],
	'private' => [
		'NAME' => Loc::getMessage('LANDING_TPL_TITLE_MENU_PRIVATE'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => 'private'], ['save']),
			'data-slider-ignore-autobinding' => 'true'
		],
		'HELP_CODE' => 'DOMAIN_EDIT'
	]
];
if (!$arResult['REGISTER']->enable())
{
	unset($menuItems['provider']);
}
if (!$tab)
{
	if ($arResult['B24_DOMAIN_NAME'])
	{
		$tab = 'bitrix24';
	}
	else if ($arResult['IS_FREE_DOMAIN'] != 'Y')
	{
		$tab = 'private';
	}
	else
	{
		$menuItemsKeys = array_keys($menuItems);
		$tab = array_shift($menuItemsKeys);
	}
}
if (isset($menuItems[$tab]))
{
	$menuItems[$tab]['ACTIVE'] = true;
}
$this->setViewTarget('left-panel');
$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrappermenu',
	'',
	[
		'ID' => 'landing-domain-left-menu',
		'ITEMS' => $menuItems,
		'TITLE' => Loc::getMessage('LANDING_TPL_TITLE_MENU')
	]
);
$this->endViewTarget();

// help link
if ($menuItems[$tab]['HELP_CODE'])
{
	$helpUrl = \Bitrix\Landing\Help::getHelpUrl(
		$menuItems[$tab]['HELP_CODE']
	);
	if ($helpUrl)
	{
		$helpText = Loc::getMessage('LANDING_TPL_HELP_LINK');
		$helpHint = Loc::getMessage('LANDING_TPL_HELP_LINK_HINT');
		$helpLink = <<<HTML
			<a class="landing-domain-link" href="$helpUrl" target="_blank">
				$helpText
				<span data-hint="$helpHint" class="ui-hint"></span>
			</a>
		HTML;

		Toolbar::addRightCustomHtml($helpLink, [
			'align' => 'right',
		]);
	}
}

// content
if (isset($menuItems[$tab]))
{
	$replace = [];
	Manager::setPageTitle($menuItems[$tab]['NAME']);
	if ($arResult['PROVIDER_SITES'] ?? null)
	{
		$anotherSite = array_shift($arResult['PROVIDER_SITES']);
		$replace = [
			'#SITE_NAME#' => $anotherSite['TITLE'],
			'#DOMAIN_NAME#' => $anotherSite['DOMAIN_NAME']
		];
	}
	if ($tab == 'provider' && $arResult['IS_FREE_DOMAIN'])
	{
		$suspendedTime = Restriction\Site::getFreeDomainSuspendedTime();
		if ($suspendedTime && $suspendedTime <= time())
		{
			?>
			<div class="landing-domain-state landing-domain-state-free">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_SUSPENDED_H1', $replace);?></div>
				<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_SUSPENDED_TEXT', $replace);?></div>
				<div class="landing-domain-state-image --locked"></div>
				<div class="landing-domain-state-free-detail">
					<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_SUSPENDED_NOTICE');?></div>
					<br/>
				</div>
				<a href="<?= SITE_DIR?>settings/license_all.php" class="ui-btn ui-btn-light-border" target="_blank">
					<?= Loc::getMessage('LANDING_TPL_TARIFF');?>
				</a>
			</div>
			<?
		}
		else if (Register::isDomainActive($arResult['DOMAIN_NAME']))
		{
			?>
			<div class="landing-domain-state landing-domain-state-success">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_AVAILABLE_TITLE');?></div>
				<div class="landing-domain-state-info">
					<span class="landing-domain-state-info-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_AVAILABLE_LABEL', ['#DOMAIN_NAME#' => $arResult['DOMAIN_NAME']]);?></span>
				</div>
				<div class="landing-domain-state-image">
					<div class="landing-domain-state-image-value"></div>
				</div>
			</div>
			<?
		}
		else
		{
			?>
			<div class="landing-domain-state landing-domain-state-wait">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_TITLE');?></div>
				<div class="landing-domain-state-info">
					<span class="landing-domain-state-info-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_LABEL', ['#DOMAIN_NAME#' => $arResult['DOMAIN_NAME']]);?></span>
				</div>
				<div class="landing-domain-state-image">
					<div class="landing-domain-state-image-value"></div>
				</div>
				<div class="landing-domain-state-notice"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_NOT_AVAILABLE_TEXT');?></div>
			</div>
			<?
		}
		return;
	}
	else if ($tab == 'provider' && !$arResult['FEATURE_FREE_AVAILABLE'] && $arResult['PROVIDER_SITES'])
	{
		?>
		<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post">
			<input type="hidden" name="action" value="switchToThis">
			<input type="hidden" name="param" value="<?= $anotherSite['ID'];?>">
			<?= bitrix_sessid_post();?>
			<div class="landing-domain-state landing-domain-state-free">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_H1', $replace);?></div>
				<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_TEXT', $replace);?></div>
				<div class="landing-domain-state-image"></div>
				<div class="landing-domain-state-free-detail">
					<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_ALERT', $replace);?></div>
					<div class="landing-domain-state-detail-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_NOTICE');?></div>
				</div>
				<button type="submit" class="ui-btn ui-btn-light-border">
					<?= Loc::getMessage('LANDING_TPL_SWITCH');?>
				</button>
			</div>
		</form>
		<?
		return;
	}
	else if ($arResult['IS_FREE_DOMAIN'])
	{
		$APPLICATION->includeComponent(
			'bitrix:landing.site_domain_switch',
			'',
			array(
				'SITE_ID' => $arParams['SITE_ID'],
				'MODE' => 'CHANGE_GIFT'
			),
			$component
		);
		return;
	}

	$currentDomain = ($tab == 'provider') ? $arResult['REGISTER']->getPortalDomains() : [];

	if ($currentDomain)
	{
		$puny = new \CBXPunycode;
		$currentDomain = $puny->decode(array_shift($currentDomain));
		$replace = [
			'#DOMAIN_NAME#' => $currentDomain
		];
		?>
		<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post">
			<input type="hidden" name="action" value="SaveProvider">
			<input type="hidden" name="param" value="<?= $currentDomain;?>">
			<?= bitrix_sessid_post();?>
			<div class="landing-domain-state landing-domain-state-free">
				<div class="landing-domain-state-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_H1');?></div>
				<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_TEXT', $replace);?></div>
				<div class="landing-domain-state-image"></div>
				<div class="landing-domain-state-free-detail">
					<div class="landing-domain-state-free-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ALREADY_EXIST_DOMAIN_ALERT', $replace);?></div>
					<div class="landing-domain-state-detail-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_ANOTHER_SITE_NOTICE');?></div>
				</div>
				<button type="submit" class="ui-btn ui-btn-light-border">
					<?= Loc::getMessage('LANDING_TPL_GET_FREE');?>
				</button>
			</div>
		</form>
		<?
		return;
	}
	?>
	<script>
		BX.ready(function()
		{
			BX.message({
				LANDING_TPL_ERROR_DOMAIN_EXIST: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST')) ?>',
				LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED')) ?>',
				LANDING_TPL_ERROR_DOMAIN_EMPTY: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY')) ?>',
				LANDING_TPL_ERROR_DOMAIN_WRONG_NAME: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_NAME')) ?>',
				LANDING_TPL_ERROR_DOMAIN_WRONG_LENGTH: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_LENGTH')) ?>',
				LANDING_TPL_ERROR_DOMAIN_WRONG_SYMBOL_COMBINATIONS: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_SYMBOL_COMBINATIONS')) ?>',
				LANDING_TPL_ERROR_DOMAIN_WRONG_DOMAIN_LEVEL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_DOMAIN_LEVEL')) ?>',
				LANDING_TPL_DOMAIN_LENGTH_LIMIT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_LENGTH_LIMIT')) ?>',
				LANDING_TPL_ALERT_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ALERT_TITLE')) ?>',
				LANDING_TPL_DOMAIN_AVAILABLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_AVAILABLE')) ?>',
				LANDING_TPL_ERROR_DOMAIN_INCORRECT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_INCORRECT')) ?>',
				LANDING_TPL_ERROR_DOMAIN_CHECK_DASH: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH')) ?>',
				LANDING_TPL_ERROR_DOMAIN_CHECK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK', ['#TLD#' => strtolower($arResult['TLD'][0])])) ?>'
			});
		});
	</script>
	<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post" class="ui-form landing-form-gray-padding">
		<input type="hidden" name="action" value="save<?= $tab;?>">
		<?= bitrix_sessid_post();?>
		<?
		include $tab . '.php';
		?>
	</form>
	<?
}