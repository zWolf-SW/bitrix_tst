<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.popup',
		'ui.vue3.vuex',
		'rest.client',
		'ui.dialogs.messagebox',
		'call.lib.analytics',
		'im.v2.lib.call',
		'im.v2.provider.service.recent',
		'im.v2.lib.invite',
		'im.public',
		'im.v2.provider.service.chat',
		'im.v2.lib.promo',
		'im.v2.lib.analytics',
		'im.v2.application.core',
		'im.v2.lib.parser',
		'im.v2.lib.entity-creator',
		'im.v2.provider.service.message',
		'im.v2.provider.service.disk',
		'im.v2.lib.market',
		'im.v2.lib.utils',
		'im.v2.lib.permission',
		'im.v2.lib.confirm',
		'im.v2.lib.notifier',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.channel',
		'main.core',
		'im.v2.lib.copilot',
	],
	'skip_core' => false,
];