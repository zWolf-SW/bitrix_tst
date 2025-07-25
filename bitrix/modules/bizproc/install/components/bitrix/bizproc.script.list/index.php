<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$cmpParams = [
	'DOCUMENT_TYPE_SIGNED' => $_GET['documentType'] ?? null,
	'PLACEMENT' => $_GET['placement'] ?? null,
	'SET_TITLE' => 'Y',
];

if ($_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.script.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $cmpParams,
			"USE_UI_TOOLBAR" => "Y",
		],
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.script.list', '', $cmpParams);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
