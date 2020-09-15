<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel;

use kernel\exception\http\InvalidDataHttpException;
use kernel\exception\http\ServerErrorHttpException;
use kernel\helpers\Cookies;
use kernel\helpers\Csrf;
use kernel\helpers\Headers;
use kernel\helpers\Request;
use kernel\helpers\Session;
use kernel\pattern\mvc\Router;

/**
 * Класс, реализуйщий шаблон проектирования "Реестр" (Registry).
 * Имеется два основных свойства: массив объектов пользовательских компонентов и массив объектов компонентов ядра.
 *
 * @package kernel\pattern
 */
class KernelRegistry extends pattern\registry\Registry {
	
	/**
	 * @var array Компоненты ядра
	 */
	private static array $kernelObjects = [];
	/**
	 * @var array|string[] Список компонентов ядра первичной инициализации
	 */
	private static array $allowedKernelObjects = [
		'config' => Config::class,
		'cookies' => Cookies::class,
		'headers' => Headers::class,
		'session' => Session::class,
		'request' => Request::class,
		'router' => Router::class,
		'csrf' => Csrf::class,
	];
	
	/**
	 * Инициализация реестра
	 *
	 * @throws ServerErrorHttpException
	 */
	public function init(): void {
		foreach (self::$allowedKernelObjects as $name => $class) {
			self::$kernelObjects[$name] = $class::getInstance();
		}
		
		parent::init();
	}
	
	/**
	 * Получение компонента.
	 * В случае отсутствия пользовательского компонента идет попытка получения компонента ядра.
	 * Если получения компонента ядра не удалось, то выдает ошибку
	 *
	 * @param string $name
	 *
	 * @return mixed
	 * @throws InvalidDataHttpException
	 */
	public function get(string $name) {
		if (!array_key_exists($name, self::$objects) && !array_key_exists($name, self::$kernelObjects)) {
			throw new InvalidDataHttpException("Отсутствует компонент {$name}");
		}
		
		return array_key_exists($name, self::$objects) ? self::$objects[$name] : self::$kernelObjects[$name];
	}
}