<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace application\assets;

use kernel\helpers\Asset;

class IconsAsset extends Asset {
	
	public array $css = [
		'icons/favicon/apple-icon-57x57.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '57x57',
		],
		'icons/favicon/apple-icon-60x60.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '60x60',
		],
		'icons/favicon/apple-icon-72x72.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '72x72',
		],
		'icons/favicon/apple-icon-76x76.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '76x76',
		],
		'icons/favicon/apple-icon-114x114.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '114x114',
		],
		'icons/favicon/apple-icon-120x120.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '120x120',
		],
		'icons/favicon/apple-icon-144x144.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '144x144',
		],
		'icons/favicon/apple-icon-152x152.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '152x152',
		],
		'icons/favicon/apple-icon-180x180.png' => [
			'rel' => 'apple-touch-icon',
			'sizes' => '180x180',
		],
		'icons/favicon/android-icon-192x192.png' => [
			'rel' => 'icon',
			'sizes' => '192x192',
			'type' => 'image/png',
		],
		'icons/favicon/android-icon-32x32.png' => [
			'rel' => 'icon',
			'sizes' => '32x32',
			'type' => 'image/png',
		],
		'icons/favicon/android-icon-96x96.png' => [
			'rel' => 'icon',
			'sizes' => '96x96',
			'type' => 'image/png',
		],
		'icons/favicon/android-icon-16x16.png' => [
			'rel' => 'icon',
			'sizes' => '16x16',
			'type' => 'image/png',
		],
		'icons/favicon/manifest.json' => [
			'rel' => 'manifest'
		],
	];
}