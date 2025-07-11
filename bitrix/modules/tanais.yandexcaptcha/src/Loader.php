<?php

declare(strict_types=1);

namespace Tanais\YandexCaptcha;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

class Loader
{
    public const YANDEX_SMARTCAPTCHA_CLOUD_URL = 'https://smartcaptcha.yandexcloud.net';

    public function loadInitScripts(): void
    {
        Asset::getInstance()->addString($this->getInitScripts());
    }

    private function getInitScripts(): string
    {
        $clientKey = Option::get('tanais.yandexcaptcha', 'CLIENT_KEY');
        $modeTest = Option::get('tanais.yandexcaptcha', 'MODE_TEST') === 'Y';
        $invisible = Option::get('tanais.yandexcaptcha', 'INVISIBLE') === 'Y';
        $shieldCustom = Option::get('tanais.yandexcaptcha', 'SHIELD_CUSTOM') === 'Y';
        $shieldPosition = Option::get('tanais.yandexcaptcha', 'SHIELD_POSITION');

        ob_start();
    ?>
        <script src="<?=self::YANDEX_SMARTCAPTCHA_CLOUD_URL?>/captcha.js?render=onload&onload=onloadTanaisYandexCaptchaInitialization" defer></script>
        <script type="text/javascript">
            const tanaisYandexCaptchaOptions = {
                sitekey: <?=Json::encode($clientKey)?>,
                hl: <?=Json::encode(LANGUAGE_ID)?>,
                test: <?=Json::encode($modeTest)?>,
                invisible: <?=Json::encode($invisible)?>,
                shieldPosition: <?=Json::encode($shieldPosition)?>,
                hideShield: <?=Json::encode($shieldCustom)?>,
            };

            BX.ready(function () {
                BX.addCustomEvent("onAjaxSuccess", function() {
                    onloadTanaisYandexCaptchaInitialization();
                    if (tanaisYandexCaptchaOptions.invisible) {
                        attachTanaisYandexCaptchaInvisibleExecutor();
                    }
                });
            });

            $(() => {
                if (tanaisYandexCaptchaOptions.invisible) {
                    attachTanaisYandexCaptchaInvisibleExecutor();
                }
            });

            // Инициализация событий отправки формы с фоновой верификацией пользователя для невидимой каптчи.
            function attachTanaisYandexCaptchaInvisibleExecutor() {
                let forms = $("form:has(.tanais-yandexcaptcha)");
                forms.each(function () {
                    // Вешаем события только на тех формах, где еще нет событий.
                    // Защита от многократного вызова функции при работе нескльких форм на странице с AJAX_MODE = Y.
                    if ($(this).attr("data-added-submit-event-handler") !== "Y") {
                        $(this).find('[type="submit"]').on("click", function (e) {
                            let captchaToken = $(this).closest("form").find('.tanais-yandexcaptcha [name="smart-token"]');

                            // При попытке отправки формы, пользователь еще не верефицирован (признак пустого токена),
                            // и необходимо отменить отправку формы, и долждаться результата верификации.
                            if (captchaToken.val() === "") {
                                e.preventDefault();
                            }

                            // Запуск верификации каптчи для конкретной формы (по идентификатору виджета).
                            let widgetId = $(this).closest("form").attr("data-yandex-captcha-id");
                            if (widgetId) {
                                window.smartCaptcha.execute(widgetId);
                            }
                        });

                        $(this).attr("data-added-submit-event-handler", "Y");
                    }
                });
            }

            // Инциализация каптчи во всех формах содержащих контейнер для yandex captcha (.tanais-yandexcaptcha).
            function onloadTanaisYandexCaptchaInitialization() {
                if (window.smartCaptcha) {
                    let forms = $("form:has(.tanais-yandexcaptcha:not([data-testid]))");
                    forms.each(function () {
                        const captchaContainer = $(this).find(".tanais-yandexcaptcha");
                        let options = tanaisYandexCaptchaOptions;

                        // Автоматическая отправка формы после верификации пользователя для невидимой каптчи
                        // верификация происходит после попытки отправки формы
                        if (options.invisible) {
                            options.callback = () => {
                                BX.ready(() => {
                                    $(this).find('[type="submit"]').trigger("click");
                                });
                            }
                        }

                        let widgetId = window.smartCaptcha.render(captchaContainer.get(0), options);

                        // Т.к. виджетов на странице может быть несколько,
                        // необходимо сохранить идентификатор виджеты для последующих манипуляций (фоновой верификации, сброса и т.д.)
                        $(this).attr("data-yandex-captcha-id", widgetId);
                    });
                }
            }
        </script>
    <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}