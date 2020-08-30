<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\pattern;

use Exception;

/**
 * Класс, реализуйщий шаблон проектирования "Одиночка" (Singleton).
 * Класс предназнаен для управления экземплярами одиночек.
 *
 * @package kernel\pattern
 */
class Singleton {
	
	/**
	 * @var array Массив объектов одиночки и экземпляров кокретных подклассов
	 */
	private static array $instances = [];
	
	/**
	 * Конструктор Одиночки.
	 * Всегда скрытым, чтобы предотвратить создание объекта через оператор new.
	 */
	protected function __construct() {
	}
	
	/**
	 * Возвращает экзепляр Singleton. Управляет доступом к экземпляру одиночки.
	 * При первом запуске, он создаёт экземпляр одиночки и помещает его в статическое поле.
	 * При последующих запусках, он возвращает клиенту объект, хранящийся в статическом поле.
	 *
	 * @return static::class
	 */
	public static function getInstance(): Singleton {
		$subclass = static::class;
		if (!isset(self::$instances[$subclass])) {
			self::$instances[$subclass] = new static;
		}
		
		return self::$instances[$subclass];
	}
	
	/**
	 * Возвращает наследников Singleton
	 *
	 * @return array
	 */
	static function getAllInstances(): array {
		return self::$instances;
	}
	
	/**
	 * Запрещает десериализацию Singleton.
	 *
	 * @throws Exception
	 */
	public function __wakeup() {
		throw new Exception("Невозможно десериализовать синглтон.");
	}
	
	/**
	 * Запрещает копирование Singleton.
	 */
	protected function __clone() {
	}
}