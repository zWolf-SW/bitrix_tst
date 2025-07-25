<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.lib.notifier',
		'im.v2.lib.permission',
		'im.v2.lib.create-chat',
		'main.core',
		'im.v2.lib.helpdesk',
		'socialnetwork.collab.access-rights',
		'main.core.events',
		'main.popup',
		'im.v2.lib.confirm',
		'im.v2.application.core',
		'im.public',
		'im.v2.lib.analytics',
		'im.v2.provider.service.chat',
		'im.v2.component.elements.avatar',
		'im.v2.const',
		'im.v2.component.content.chat-forms.elements',
	],
	'skip_core' => false,
];
