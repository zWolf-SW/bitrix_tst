<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var array $arResult */
/** @var array $arParams */

foreach ($arResult['ERRORS'] as $errorCode => $error)
{
	break;
}

if (empty($error))
{
	return;
}
$groupPath = '';

Manager::setPageView(
	'MainTag',
	'style="height: 100vh; background: #fff;"'
);

Extension::load([
	'ui.fonts.opensans', 'ui.buttons'
]);
?>

	<!-- ico 'SITE was not found' -->
	<div class="landing-error-site">
		<div class="landing-error-site-img">
			<div class="landing-error-site-img-inner"></div>
		</div>
		<div class="landing-error-site-title"><?= $error ?></div>
		<div class="landing-error-site-desc">
			<?= Loc::getMessage('LANDING_TPL_ERROR_NOT_FOUND_NOTE', [
				'#LINK1#' => '<a href="' . ($arResult['SITE_URL'] ?? '/') . '">',
				'#LINK2#' => '</a>',
			]);?>
		</div>
	</div>

<script>
	(function()
	{
		if (window.location.hash.indexOf('#landingId') === 0)
		{
			window.location.href = BX.Uri.addParam(
				window.location.href,
				{
					forceLandingId: window.location.hash.substr(
						'#landingId'.length
					)
				}
			);
			window.location.hash = '';
		}
	})();
</script>