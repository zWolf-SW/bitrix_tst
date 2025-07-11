<?php

declare(strict_types=1);

namespace Tanais\YandexCaptcha\EventHandler;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Application;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Config\Option;

use Tanais\YandexCaptcha\Loader;

class Main
{
    public static function onEndBufferContent(string &$content): void
    {
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->isAdminSection()) {
            return;
        }

        $content = preg_replace('/<input[^<>]*name\s?=\s?.captcha_word.[^<>]*>/', '', $content);
        $content = preg_replace(
            '/<img[^<>]*src\s?=\s?.\/bitrix\/tools\/captcha\.php\?(captcha_code|captcha_sid)=[^<>]*>/',
            '<div class="tanais-yandexcaptcha"></div>',
            $content
        );
    }

    public static function onPageStart(): void
    {
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->isAdminSection()) {
            return;
        }

        $loader = new Loader();
        $loader->loadInitScripts();

        $checkCaptcha = function ($token) use ($request)
        {
            $httpClient = new HttpClient();
            $httpClient->setTimeout(3);

            $args = http_build_query([
                "secret" => Option::get('tanais.yandexcaptcha', 'SERVER_KEY'),
                "token" => $token,
                "ip" => $request->getRemoteAddress(),
            ]);

            $httpClient->get(Loader::YANDEX_SMARTCAPTCHA_CLOUD_URL . "/validate?$args");

            if ($httpClient->getStatus() !== 200)
            {
                if (Option::get('tanais.yandexcaptcha', 'ERROR_LOG') === 'Y')
                {
                    Debug::writeToFile(
                        sprintf(
                            'Allow access due to an error: %s; message=%s',
                            $httpClient->getStatus(),
                            $httpClient->getResult()
                        ),
                        date('d.m.Y H:i:s'),
                        getLocalPath('modules/tanais.yandexcaptcha') . '/error.log'
                    );
                }

                return false;
            }

            $result = json_decode($httpClient->getResult(), true);
            if ($result !== null)
            {
                return $result['status'] === 'ok';
            }

            return false;
        };

        $smartToken = $request->get('smart-token');
        if ($smartToken)
        {
            if (!$checkCaptcha($smartToken))
            {
                return;
            }

            $captchaSid = $request->get('captcha_sid');
            if ($captchaSid !== null)
            {
                $connection = Application::getConnection();
                $bCaptchaResult = $connection->query('SELECT `CODE` FROM `b_captcha` WHERE `id`="' . $connection->getSqlHelper()->forSql($captchaSid) . '"');

                if ($bCaptchaData = $bCaptchaResult->fetch())
                {
                    $request->modifyByQueryString('captcha_word=' . $bCaptchaData['CODE']);
                    $_REQUEST['captcha_word'] = $_POST['captcha_word'] = $bCaptchaData['CODE'];
                }
            }
        }
    }
}