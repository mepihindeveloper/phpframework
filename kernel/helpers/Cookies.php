<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Config;
use kernel\exception\http\InvalidDataHttpException;
use kernel\pattern\Singleton;

/**
 * Касс-помощник для работы с Cookies.
 * Класс реализует шаблон проектирования "Строитель" в области добавления значений.
 *
 * @package kernel\helpers
 */
class Cookies extends Singleton {
	
	/**
	 * @var array Конфигурация cookie
	 */
	private array $cookieSettings;
	/**
	 * @var array Cookie
	 */
	private array $cookie;
	
	protected function __construct() {
		parent::__construct();
		$this->cookieSettings = Config::getInstance()->getActiveSettings('cookies');
	}
	
	/**
	 * Удаляет cookie по названию
	 *
	 * @param string $key Название cookie
	 *
	 * @return void
	 */
	public function remove(string $key): void {
		setcookie($key, '', 1);
		unset($_COOKIE[$key]);
	}
	
	/**
	 * Проверяет наличие cookie
	 *
	 * @param string $key Название cookie
	 *
	 * @return bool
	 */
	public function has(string $key): bool {
		return array_key_exists($key, $_COOKIE);
	}
	
	/**
	 * Получает значение cookie
	 *
	 * @param string $key Название cookie
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		return $_COOKIE[$key];
	}
	
	/**
	 * Получает все cookie
	 *
	 * @return array
	 */
	public function getAll(): array {
		return $_COOKIE;
	}
	
	/**
	 * Удаляет все cookie
	 *
	 * @return void
	 */
	public function removeAll(): void {
		foreach ($_COOKIE as $cookie => $value) {
			setcookie($cookie, '', 1);
		}
		
		unset($_COOKIE);
		$_COOKIE = [];
	}
	
	/**
	 * Назначает значение cookie
	 *
	 * @param string $name Название cookie
	 * @param string $value Значение cookie
	 *
	 * @return Cookies
	 */
	public function setValue(string $name, string $value): Cookies {
		$this->cookie[] = ['name' => $name, 'value' => $value];
		
		return $this;
	}
	
	/**
	 * Назначает значения cookie
	 *
	 * @param array $cookies Массив названий и значений cookies. Данные передаются парой ключ-значение,
	 * где ключ - название cookies, а значение - содержимое cookies.
	 *
	 * @return $this
	 */
	public function setValues(array $cookies): Cookies {
		foreach ($cookies as $name => $value) {
			$this->cookie[] = ['name' => $name, 'value' => $value];
		}
		
		return $this;
	}
	
	/**
	 * Устанавливает срок жизни cookie
	 *
	 * @param int $value Срок жизни cookie
	 *
	 * @return Cookies
	 */
	public function setExpire(int $value): Cookies {
		$this->cookie['expire'] = $value;
		
		return $this;
	}
	
	/**
	 * Устанавливает путь к директории на сервере, из которой будут доступны cookie
	 *
	 * @param string $value Путь к директории cookie
	 *
	 * @return Cookies
	 */
	public function setPath(string $value): Cookies {
		$this->cookie['path'] = $value;
		
		return $this;
	}
	
	/**
	 * Устанавливает (под)домен, которому доступны cookie
	 *
	 * @param string $value Домен
	 *
	 * @return Cookies
	 */
	public function setDomain(string $value): Cookies {
		$this->cookie['domain'] = $value;
		
		return $this;
	}
	
	/**
	 * Устанавливает защищенное соединение
	 *
	 * @param bool $value Активность защищенного соединения (HTTPS)
	 *
	 * @return Cookies
	 */
	public function setSecure(bool $value): Cookies {
		$this->cookie['secure'] = $value;
		
		return $this;
	}
	
	/**
	 * Устанавливает доступ только через HTTP-протокол
	 *
	 * @param bool $value Активность доступна только через HTTP-протокол
	 *
	 * @return Cookies
	 */
	public function setHttponly(bool $value): Cookies {
		$this->cookie['httponly'] = $value;
		
		return $this;
	}
	
	/**
	 * Добавляет cookie
	 *
	 * @return void
	 *
	 * @throws InvalidDataHttpException
	 */
	public function add(): void {
		foreach ($this->cookie as $cookie) {
			if (!array_key_exists('name', $cookie)) {
				throw new InvalidDataHttpException('Отсутствует название cookie');
			}
			
			$cookieParams = [
				'name' => $cookie['name'],
				'value' => $cookie['value']
			];
			
			foreach (['expire', 'path', 'domain', 'secure', 'httponly'] as $param) {
				$cookieParams[$param] = array_key_exists($param, $cookie) ? $cookie[$param] : $this->cookieSettings[$param];
			}
			
			setcookie(
				$cookieParams['name'],
				$cookieParams['value'],
				$cookieParams['expire'],
				$cookieParams['path'],
				$cookieParams['domain'],
				$cookieParams['secure'],
				$cookieParams['httponly']
			);
		}
	}
}