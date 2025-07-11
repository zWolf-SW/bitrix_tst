<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

$module_id = "tanais.yandexcaptcha";

if ($APPLICATION->GetGroupRight($module_id) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::includeModule($module_id);

$request = Application::getInstance()->getContext()->getRequest();

$aTabs = [
    "COMMON" => [
        "DIV" => "COMMON",
        "TAB" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_COMMON_TITLE"),
        "OPTIONS" => [
            Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_KEYS_TITLE"),
            [
                'note' => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_KEYS_NOTE"),
            ],
            [
                "CLIENT_KEY",
                Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_CLIENT_KEY"),
                "",
                ["text", 0]
            ],
            [
                "SERVER_KEY",
                Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SERVER_KEY"),
                "",
                ["text", 0]
            ],
            Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_VIEW_TITLE"),
            [
                'note' => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_VIEW_NOTE"),
            ],
            [
                "INVISIBLE",
                Loc::getMessage(
                    "TANAIS_YANDEXCAPTCHA_OPTION_INVISIBLE"
                ),
                "N",
                ["checkbox"]
            ],
            [
                "SHIELD_CUSTOM",
                Loc::getMessage(
                    "TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_CUSTOM"
                ),
                "N",
                ["checkbox"]
            ],
            [
              "SHIELD_POSITION",
                Loc::getMessage(
                    "TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION"
                ),
                "bottom-right",
                [
                    "selectbox",
                    [
                        "top-left" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_TOP_LEFT"),
                        "center-left" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_CENTER_LEFT"),
                        "bottom-left" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_BOTTOM_LEFT"),
                        "top-right" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_TOP_RIGHT"),
                        "center-right" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_CENTER_RIGHT"),
                        "bottom-right" => Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SHIELD_POSITION_BOTTOM_RIGHT"),
                    ]
                ]
            ],
            Loc::getMessage("TANAIS_YANDEXCAPTCHA_OPTION_SERVICE_TITLE"),
            [
                "ERROR_LOG",
                Loc::getMessage(
                    "TANAIS_YANDEXCAPTCHA_OPTION_ERROR_LOG"
                ),
                "Y",
                ["checkbox"]
            ],
            [
                "MODE_TEST",
                Loc::getMessage(
                    "TANAIS_YANDEXCAPTCHA_OPTION_MODE_TEST"
                ),
                "N",
                ["checkbox"]
            ],
        ],
    ],

    "RIGHTS" => [
        "DIV" => "rights",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
        "OPTIONS" => [],
    ],
];



if ($request->isPost() && $request["Apply"] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab)
    {
        foreach ($aTab["OPTIONS"] as $arOption)
        {
            if (!is_array($arOption)) {
                continue;
            }

            if ($arOption["note"]) {
                continue;
            }

            $optionName = $arOption[0];
            $optionType = $arOption[3][0];

            if ($optionType == "statichtml") {
                continue;
            }

            $optionValue = $request->getPost($optionName);

            if ($optionType == "checkbox" && $optionValue == "") {
                $optionValue = "N";
            }

            Option::set(
                $module_id,
                $optionName,
                is_array($optionValue) ? implode(",", $optionValue) : $optionValue
            );
        }
    }
}

$tabControl = new CAdminTabControl("tabControl", array_values($aTabs));
?>


<script type="text/javascript">
    window.addEventListener('load', function () {
        let arDependencies = [
            {
                "field": {
                    "type": "checkbox",
                    "value": true,
                    "name": "INVISIBLE"
                },
                "dependent": [
                    "[name=SHIELD_CUSTOM]",
                    "[name=SHIELD_POSITION]",
                ]
            },
        ];


        for (let key in arDependencies) {
            if (arDependencies.hasOwnProperty(key)) {
                let item = arDependencies[key];
                let input = document.querySelector(".tanais-yandexcaptcha-form [name='" + item.field.name + "']");

                if (item.field.type === "checkbox") {
                    input.onchange = (function (value, list)
                    {
                        return function () {
                            toggle(this, list, this.checked !== value);
                        };
                    })(item.field.value, item.dependent);

                    toggle(input, item.dependent, input.checked !== item.field.value);
                }
                else if (item.field.type === "select")
                {
                    input.onchange = (function (value, list) {
                        return function () {
                            toggle(this, list, this.value !== value);
                        };
                    })(item.field.value, item.dependent)

                    toggle(input, item.dependent, input.value !== item.field.value);
                }
            }
        }

        function toggle(_this, list, disable) {
            for (let i = 0; i < list.length; i++) {
                let item = _this.form.querySelector(list[i]);
                if (item.tagName === "INPUT" || item.tagName === "SELECT") {
                    item.disabled = disable;
                }
                else {
                    if (disable) {
                        item.classList.add("disable");
                    }
                    else {
                        item.classList.remove("disable");
                    }
                }
            }
        }
    });
</script>

<?php
$tabControl->Begin();
?>
<form class='tanais-yandexcaptcha-form' method='post'
      action='<? echo $APPLICATION->GetCurPage() ?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request["lang"]?>'
      name='tanais_yandexcaptcha_settings'>
    <?php
    foreach ($aTabs as $aTab):
        if ($aTab["OPTIONS"]):
            $tabControl->BeginNextTab();
            foreach($aTab["OPTIONS"] as $key => $option)
            {
                __AdmSettingsDrawRow($module_id, $option);
            }
        endif;
    endforeach;
    ?>

    <?php
    $tabControl->BeginNextTab();

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");

    $tabControl->Buttons();
    ?>

    <input type="submit" name="Apply" value="<?=Loc::getMessage("MAIN_SAVE") ?>">
    <input type="hidden" name="Update" value="Y">
    <input type="reset" name="reset" value="<?=Loc::getMessage("MAIN_RESET") ?>">
    <?=bitrix_sessid_post();?>
</form>
<?php $tabControl->End(); ?>