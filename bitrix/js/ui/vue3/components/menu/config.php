<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/menu.bundle.js',
	'rel' => [
		'main.core',
		'ui.system.menu',
	],
	'skip_core' => false,
];
