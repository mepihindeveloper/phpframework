<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\KernelRegistry;
use kernel\pattern\Singleton;

class Asset extends Singleton {
	
	public const TYPE_CSS = 'css';
	public const TYPE_JS = 'js';
	/**
	 * @var string Корневая папка ресурсов. Значение по умолчанию берется из настроек поля resourceRoot.
	 */
	public static string $resourceRoot = '';
	/**
	 * @var array|string[] Параметры по умолчанию для css и js файлов
	 */
	protected static array $defaultParams = [
		'css' => [
			'rel' => 'stylesheet',
			'type' => 'text/css',
		],
		'js' => [
			'type' => 'application/javascript',
		]
	];
	/**
	 * @var array Карта ресурсов
	 */
	private static array $map;
	/**
	 * @var array Пути и настройки к css файлам. Настройки задаются парой ключ => значение.
	 * Пример: ['path/to/css.css => ['param' => 'value']'] или ['path/to/css.css', 'path/to/css2.css]
	 */
	public array $css = [];
	/**
	 * @var array Пути и настройки к js файлам. Настройки задаются парой ключ => значение.
	 * Пример: ['path/to/js.js => ['param' => 'value']'] или ['path/to/js.js', 'path/to/js2.js]
	 */
	public array $js = [];
	
	public function __construct() {
		parent::__construct();
		
		self::$resourceRoot = KernelRegistry::getInstance()->get('config')->getSection('resourceRoot');
	}
	
	/**
	 * Регистрирует html код для подключаемых файлов ресурсов
	 *
	 * @param string $type Тип файла подключения. Выбирается из TYPE_CSS и TYPE_JS
	 * @param string|null $section Секция подключения
	 *
	 * @return string
	 */
	public static function register(string $type, ?string $section = null): string {
		$assetClass = static::class;
		$asset = new $assetClass;
		self::$resourceRoot = $asset::$resourceRoot;
		self::$map = ['css' => $asset->css, 'js' => $asset->js];
		$map = is_null($section) ? self::$map[$type] : self::$map[$type][$section];
		$html = '';
		
		foreach ($map as $key => $value) {
			$keyIsNumeric = is_numeric($key);
			$params = $keyIsNumeric
				? self::$defaultParams[$type]
				: array_merge(self::$defaultParams[$type], $value);
			$href = self::$resourceRoot . ($keyIsNumeric ? $value : $key);
			$openTag = ($type === self::TYPE_CSS ? "<link href='{$href}' " : "<script src='{$href}' ");
			$html .= $openTag . self::convertArrayParamsToString($params) . '>';
		}
		
		return $html;
	}
	
	/**
	 * Конвертирует массив параметров в строку
	 *
	 * @param array $params Массив параметров
	 *
	 * @return string
	 */
	private static function convertArrayParamsToString(array $params): string {
		$stringParams = '';
		
		array_walk($params, static function($paramValue, $paramName) use (&$stringParams) {
			$stringParams .= "{$paramName}='{$paramValue}' ";
		});
		
		return $stringParams;
	}
}