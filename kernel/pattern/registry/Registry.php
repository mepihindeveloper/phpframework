<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\pattern\registry;

use kernel\Config;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;
use kernel\pattern\Singleton;

/**
 * Класс, реализуйщий шаблон проектирования "Реестр" (Registry).
 * Имеется основное свойство - массив объектов пользовательских компонентов.
 *
 * @package kernel\pattern
 */
class Registry extends Singleton implements RegistryInterface {
	
	/**
	 * @var array Пользовательские компоненты
	 */
	protected static array $objects = [];
	
	/**
	 * @inheritDoc
	 *
	 * @throws ServerErrorHttpException
	 */
	public function init(): void {
		foreach (Config::getInstance()->getSection('components') as $name => $class) {
			self::$objects[$name] = is_subclass_of($class, Singleton::class) ?
				$class::getInstance() : new $class;
		}
	}
	
	/**
	 * @inheritDoc
	 *
	 * @throws InvalidDataHttpException
	 */
	public function get(string $name) {
		if (!array_key_exists($name, self::$objects)) {
			throw new InvalidDataHttpException("Отсутствует компонент {$name}");
		}
		
		return self::$objects[$name];
	}
}