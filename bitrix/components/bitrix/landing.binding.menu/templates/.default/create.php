<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */

$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();

if (!empty($arResult['ERRORS']))
{
	showError(implode("\n", $arResult['ERRORS']));

	return;
}
?>

<?php
if ($template = $request->get('tpl'))
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.demo_preview',
		'.default',
		array(
			'CODE' => $template,
			'TYPE' => $arParams['TYPE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'MENU',
			'BINDING_ID' => $arParams['MENU_ID'],
		),
		$component
	);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.demo',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PATH_AFTER_CREATE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'MENU',
			'BINDING_ID' => $arParams['MENU_ID'],
		),
		$component
	);
}
?>
