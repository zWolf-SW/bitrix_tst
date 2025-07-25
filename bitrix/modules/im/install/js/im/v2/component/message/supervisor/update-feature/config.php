<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/update-feature.bundle.css',
	'js' => 'dist/update-feature.bundle.js',
	'rel' => [
		'im.v2.component.elements.button',
		'im.v2.component.message.supervisor.base',
		'main.core',
		'im.v2.lib.analytics',
		'im.v2.lib.helpdesk',
	],
	'skip_core' => false,
];