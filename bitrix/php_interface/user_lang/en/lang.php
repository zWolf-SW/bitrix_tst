<?php
### <TANAIS.YANDEXCAPTCHA> ###
if (!defined('WIZARD_DEFAULT_TONLY') && Bitrix\Main\Loader::includeModule('tanais.yandexcaptcha'))
{
	$message = 'You have not passed the automated message check.';

	$MESS['/bitrix/modules/main/lang/ru/classes/general/user.php']["main_user_captcha_error"] = $message;
	$MESS['/bitrix/modules/main/lang/ru/classes/general/user.php']['MAIN_FUNCTION_REGISTER_CAPTCHA'] = $message;

	$MESS['/bitrix/components/bitrix/main.feedback/lang/ru/component.php']['MF_CAPTHCA_EMPTY'] = $message;
	$MESS['/bitrix/components/bitrix/main.feedback/lang/ru/component.php']["MF_CAPTCHA_WRONG"] = $message;

    $MESS['/bitrix/modules/form/lang/ru/include.php']["FORM_WRONG_CAPTCHA"] = $message;
    $MESS['/bitrix/components/bitrix/iblock.element.add.form/lang/ru/component.php']["IBLOCK_FORM_WRONG_CAPTCHA"] = $message;
    $MESS['/bitrix/components/bitrix/catalog.product.subscribe/lang/ru/ajax.php']["CPSA_INCCORECT_INPUT_CAPTHA"] = $message;
    $MESS['/bitrix/components/bitrix/main.register/lang/ru/component.php']["REGISTER_WRONG_CAPTCHA"] = $message;
    $MESS['/bitrix/modules/vote/lang/ru/classes/general/vote.php']['VOTE_BAD_CAPTCHA'] = $message;
}
### </TANAIS.YANDEXCAPTCHA> ###
?>