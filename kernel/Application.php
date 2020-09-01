<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel;

use kernel\helpers\Cookies;
use kernel\helpers\Headers;
use kernel\helpers\Request;
use kernel\helpers\Session;
use kernel\pattern\mvc\Router;
use kernel\pattern\Singleton;

/**
 * Класс для работы с приложением.
 * Класс предназначен для хранения необходимых компонентов. Является точкой входа в приложение и его центральной частью.
 *
 * @package kernel
 */
class Application extends Singleton {
	
	/**
	 * @var NamespaceAutoloader Объект класса автоматического загрузчика классов
	 */
	private NamespaceAutoloader $namespaceAutoloader;
	private array $defaultClassFoldersMap = [
		'classes' => ROOT . 'classes/',
		'application' => APPLICATION,
	];
	
	/**
	 * Инициализация приложения
	 *
	 * @param NamespaceAutoloader $namespaceAutoloader Объекта класса автозагрузки пространств имен
	 *
	 * @return void
	 *
	 * @throws exception\ServerErrorHttpException
	 * @throws exception\InvalidDataHttpException
	 */
	public function run(NamespaceAutoloader $namespaceAutoloader): void {
		if (!isset($this->namespaceAutoloader)) {
			$this->namespaceAutoloader = $namespaceAutoloader;
			$this->autoload($this);
		}
		
		KernelRegistry::getInstance()->init();
		KernelRegistry::getInstance()->get('router')->init();
	}
	
	/**
	 * Загружает подключаемые классы
	 *
	 * @param Application $application Приложение
	 *
	 * @return void
	 */
	private function autoload(Application $application): void {
		foreach ($this->defaultClassFoldersMap as $namespace => $root) {
			$application->namespaceAutoloader->add($namespace, $root);
		}
		
		$application->namespaceAutoloader->register();
	}
	
	/**
	 * Возвращает Объект класса управления конфигурацией
	 *
	 * @return Config
	 * @throws exception\InvalidDataHttpException
	 */
	public function getConfig(): Config {
		return KernelRegistry::getInstance()->get('config');
	}
	
	/**
	 * Возврщает объект класса управления маршрутизацией
	 *
	 * @return Router
	 * @throws exception\InvalidDataHttpException
	 */
	public function getRouter(): Router {
		return KernelRegistry::getInstance()->get('router');
	}
	
	/**
	 * Возвращает объект класса управления Cookies
	 *
	 * @return Cookies
	 * @throws exception\InvalidDataHttpException
	 */
	public function getCookies(): Cookies {
		return KernelRegistry::getInstance()->get('cookies');
	}
	
	/**
	 * Возвращает объект класса управления заголовками
	 *
	 * @return Headers
	 * @throws exception\InvalidDataHttpException
	 */
	public function getHeaders(): Headers {
		return KernelRegistry::getInstance()->get('headers');
	}
	
	/**
	 * Возвращает объект класса управления запросами
	 *
	 * @return Request
	 * @throws exception\InvalidDataHttpException
	 */
	public function getRequest(): Request {
		return KernelRegistry::getInstance()->get('request');
	}
	
	/**
	 * Возращает объект класса управления сессией
	 *
	 * @return Session
	 * @throws exception\InvalidDataHttpException
	 */
	public function getSession(): Session {
		return KernelRegistry::getInstance()->get('session');
	}
	
	/**
	 * Возвращает карту для соответствия namespace пути в файловой системе
	 *
	 * @return array
	 */
	public function getClassMap(): array {
		return $this->namespaceAutoloader->getMap();
	}
}