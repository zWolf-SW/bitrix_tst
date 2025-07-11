<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['columns', 'widgets_image'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD_IMG'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD'),
				'type' => ['background-color'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_6_NODE_CARD_TITLE'),
				'type' => ['typo'],
			],
		],
	],
];

return $return;