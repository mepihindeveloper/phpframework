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
use kernel\exception\http\InvalidDataHttpException;
use kernel\exception\http\ServerErrorHttpException;
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
		foreach (Config::getInstance()->getSection('components') as $name => $value) {
			self::$objects[$name] = is_subclass_of($value['class'], Singleton::class) ?
				$value['class']::getInstance() : new $value['class'];
			
			if (array_key_exists('params', $value)) {
				foreach ($value['params'] as $property => $propertyValue) {
					self::$objects[$name]->$property = $propertyValue;
				}
			}
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