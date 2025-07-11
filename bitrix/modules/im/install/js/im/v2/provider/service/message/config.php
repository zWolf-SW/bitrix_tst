<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/message.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.user',
		'im.v2.lib.copilot',
		'im.v2.lib.analytics',
		'main.core.events',
		'im.v2.lib.utils',
		'im.v2.lib.rest',
		'ui.vue3.vuex',
		'rest.client',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.lib.notifier',
	],
	'skip_core' => true,
];
