<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\exception\InvalidDataHttpException;
use kernel\pattern\Singleton;

/**
 * Class Headers
 *
 * @package kernel\helpers
 */
class Headers extends Singleton {
	
	/**
	 * @var array Заголовки
	 */
	private array $headers;
	
	public function __construct() {
		$this->headers = $this->getAllHeaders();
	}
	
	/**
	 * Получает все заголовки методами apache и nginx
	 *
	 * @return array
	 */
	private function getAllHeaders(): array {
		if (!function_exists('getallheaders')) {
			if (!is_array($_SERVER)) {
				return [];
			}
			
			$headers = [];
			
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			
			return $headers;
		}
		
		return getallheaders() ? getallheaders() : [];
	}
	
	/**
	 * Добавляет заголовок. Если заголовок уже существует, то он будет перезаписан.
	 *
	 * @param array $params Заголовки [key => value]
	 *
	 * @return void
	 */
	public function add(array $params): void {
		foreach ($params as $header => $value) {
			$headerExists = in_array($header, array_keys($this->headers));
			$this->headers[$header] = $value;
			
			header("{$header}: {$value}", $headerExists);
		}
	}
	
	/**
	 * Удаляет заголовок
	 *
	 * @param string $key Заголовок
	 *
	 * @return void
	 */
	public function remove(string $key): void {
		$this->getAll();
		
		unset($this->headers[$key]);
		header_remove($key);
	}
	
	/**
	 * Получает все заголовки
	 *
	 * @return array
	 */
	public function getAll(): array {
		$this->headers = !empty($this->headers) ? $this->headers : $this->getAllHeaders();
		
		return $this->headers;
	}
	
	/**
	 * Удаляет все заголовки
	 *
	 * @return void
	 */
	public function removeAll(): void {
		$this->headers = [];
		header_remove();
	}
	
	/**
	 * Получает значение заголовка
	 *
	 * @param string $key Заголовок
	 *
	 * @return string
	 *
	 * @throws InvalidDataHttpException
	 */
	public function get(string $key): string {
		if (!$this->has($key)) {
			throw new InvalidDataHttpException("Заголоков {$key} отсутсвует.");
		}
		
		return $this->headers[$key];
	}
	
	/**
	 * Проверяет наличие заголовка. Проверка идет на наличие ключа и значения
	 *
	 * @param string $key Заголовок
	 *
	 * @return bool
	 */
	public function has(string $key): bool {
		$this->getAll();
		
		return isset($this->headers[$key]);
	}
	
	/**
	 * Устанавливает заголовок(и)
	 *
	 * @param array $params Заголовок(и) [key => value]
	 *
	 * @return void
	 */
	public function set(array $params): void {
		$this->getAll();
		
		foreach ($params as $header => $value) {
			$this->headers[$header] = $value;
		}
	}
}