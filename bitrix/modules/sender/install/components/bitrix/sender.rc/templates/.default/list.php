<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:sender.rc.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
			'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'] ?? '',
			'PATH_TO_LIST' => $arResult['PATH_TO_LIST'] ?? '',
			'PATH_TO_ADD' => $arResult['PATH_TO_ADD'] ?? '',
			'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'] ?? '',
			'PATH_TO_TIME' => $arResult['PATH_TO_TIME'] ?? '',
			'PATH_TO_STAT' => $arResult['PATH_TO_STAT'] ?? '',
		],
		"USE_UI_TOOLBAR" => "Y",
	]
);