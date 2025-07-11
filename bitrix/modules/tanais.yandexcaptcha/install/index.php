<?php

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Tanais\YandexCaptcha\EventHandler;
use Bitrix\Main\EventManager as em;

Loc::loadMessages(__FILE__);

class tanais_yandexcaptcha extends CModule
{
    public $MODULE_ID = 'tanais.yandexcaptcha';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . "/version.php";

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("TANAIS_YANDEXCAPTCHA_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TANAIS_YANDEXCAPTCHA_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("TANAIS_YANDEXCAPTCHA_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TANAIS_YANDEXCAPTCHA_PARTNER_URI");
    }

    function InstallDB($arParams = [])
    {
    }

    function UnInstallDB($arParams = [])
    {
        Option::delete($this->MODULE_ID);
    }

    function InstallEvents()
    {
        em::getInstance()->registerEventHandler(
            "main",
            "OnPageStart",
            $this->MODULE_ID,
            EventHandler\Main::class,
            "onPageStart"
        );

        em::getInstance()->registerEventHandler(
            "main",
            "OnEndBufferContent",
            $this->MODULE_ID,
            EventHandler\Main::class,
            "onEndBufferContent"
        );

        return true;
    }

    function UnInstallEvents()
    {
        em::getInstance()->unRegisterEventHandler(
            "main",
            "OnPageStart",
            $this->MODULE_ID,
            EventHandler\Main::class,
            "onPageStart"
        );

        em::getInstance()->unRegisterEventHandler(
            "main",
            "OnEndBufferContent",
            $this->MODULE_ID,
            EventHandler\Main::class,
            "onEndBufferContent"
        );

        return true;
    }

    function InstallFiles($arParams = [])
    {
        foreach (['ru', 'en'] as $lang)
        {
            $realLangFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/user_lang/" . $lang . "/lang.php";
            $installLangFile = $_SERVER['DOCUMENT_ROOT'] . getLocalPath("modules/" . $this->MODULE_ID . "/install/php_interface/user_lang/" . $lang . "/lang.php");

            if (file_exists($realLangFile))
            {
                $realLangFileContent = file_get_contents($realLangFile);
                if(!preg_match("/###\s+<TANAIS.YANDEXCAPTCHA>\s+###/is", $realLangFileContent))
                {
                    $realLangFileContent = preg_replace("/\?>$/", "", rtrim($realLangFileContent));
                    $installLangFileContent = file_get_contents($installLangFile);

                    file_put_contents(
                        $realLangFile,
                        $realLangFileContent . "\n?>\n" . $installLangFileContent
                    );
                }
            } else {
                CheckDirPath($_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/user_lang/".$lang."/");
                CopyDirFiles($installLangFile, $realLangFile, true, true);
            }
        }

        return true;
    }

    function UnInstallFiles()
    {
        foreach (['ru', 'en'] as $lang)
        {
            $realLangFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/user_lang/" . $lang . "/lang.php";
            if (file_exists($realLangFile))
            {
                $realLangFileContent = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/user_lang/". $lang ."/lang.php");
                $realLangFileContent = preg_replace("/###\s+<TANAIS.YANDEXCAPTCHA>\s+###(.+?)###\s+<\/TANAIS.YANDEXCAPTCHA>\s+###/is", '', $realLangFileContent);

                RewriteFile($realLangFile, $realLangFileContent);
            }
        }

        return true;
    }

    function isVersionD7(): bool
    {
        return CheckVersion(
            ModuleManager::getVersion("main"),
            "14.00.00"
        );
    }

    function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(
                Application::getDocumentRoot(),
                "",
                dirname(__DIR__)
            );
        } else {
            return dirname(__DIR__);
        }
    }

    function isTypeSite()
    {
        return Directory::isDirectoryExists(
            $path = $this->GetPath() . "/install/wizards"
        );
    }

    function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7())
        {
            ModuleManager::registerModule($this->MODULE_ID);

            if (!$this->isTypeSite()) {
                $this->InstallDB();
            }

            $this->InstallEvents();
            $this->InstallFiles();
        } else {
            $APPLICATION->ThrowException(
                Loc::getMessage("TANAIS_YANDEXCAPTCHA_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("TANAIS_YANDEXCAPTCHA_INSTALL"),
            $this->GetPath() . "/install/step.php"
        );
    }

    function DoUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        $this->UnInstallFiles();
        $this->UnInstallEvents();

        if ($request["savedata"] != "Y") {
            $this->UnInstallDB();
        }

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("TANAIS_YANDEXCAPTCHA_UNINSTALL"),
            $this->GetPath() . "/install/unstep.php"
        );
    }
}

?>
