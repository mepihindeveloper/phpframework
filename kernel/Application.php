<?php
declare(strict_types = 1);

namespace kernel;

use kernel\exception\NotFoundHttpException;
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
	 * @var Config Объект класса управления конфигурацией
	 */
	private Config $config;
	/**
	 * @var Router Объект класса управления маршрутизацией
	 */
	private Router $router;
	/**
	 * @var Cookies Объект класса управления Cookies
	 */
	private Cookies $cookies;
	/**
	 * @var Headers Объект класса управления заголовками
	 */
	private Headers $headers;
	/**
	 * @var Request Объект класса управления запросами
	 */
	private Request $request;
	/**
	 * @var Session Объект класса управления сессией
	 */
	private Session $session;
	/**
	 * @var NamespaceAutoloader Объект класса автоматического загрузчика классов
	 */
	private NamespaceAutoloader $namespaceAutoloader;
	
	/**
	 * Инициализация приложения
	 *
	 * @param NamespaceAutoloader $namespaceAutoloader Объекта класса автозагрузки пространств имен
	 *
	 * @return void
	 *
	 * @throws NotFoundHttpException
	 */
	public function run(NamespaceAutoloader $namespaceAutoloader): void {
		if (!isset($this->namespaceAutoloader)) {
			$this->namespaceAutoloader = $namespaceAutoloader;
			$this->autoload($this);
		}
		
		$this->config = $this->config ?? Config::getInstance();
		
		if (!ISCLI) {
			$this->cookies = $this->cookies ?? Cookies::getInstance();
			$this->headers = $this->headers ?? Headers::getInstance();
			$this->session = $this->session ?? Session::getInstance();
			$this->request = $this->request ?? Request::getInstance();
			
			$this->router = $this->router ?? new Router();
			$this->router->init();
		}
	}
	
	/**
	 * Загружает подключаемые классы
	 *
	 * @param Application $application Приложение
	 *
	 * @return void
	 */
	private function autoload(Application $application): void {
		$application->namespaceAutoloader->add('classes', ROOT . 'classes/');
		$application->namespaceAutoloader->add('controllers', ROOT . 'controllers/');
		$application->namespaceAutoloader->register();
	}
	
	/**
	 * Возвращает Объект класса управления конфигурацией
	 *
	 * @return Config
	 */
	public function getConfig(): Config {
		return $this->config;
	}
	
	/**
	 * Возврщает объект класса управления маршрутизацией
	 *
	 * @return Router
	 */
	public function getRouter(): Router {
		return $this->router;
	}
	
	/**
	 * Возвращает объект класса управления Cookies
	 *
	 * @return Cookies
	 */
	public function getCookies(): Cookies {
		return $this->cookies;
	}
	
	/**
	 * Возвращает объект класса управления заголовками
	 *
	 * @return Headers
	 */
	public function getHeaders(): Headers {
		return $this->headers;
	}
	
	/**
	 * Возвращает объект класса управления запросами
	 *
	 * @return Request
	 */
	public function getRequest(): Request {
		return $this->request;
	}
	
	/**
	 * Возращает объект класса управления сессией
	 *
	 * @return Session
	 */
	public function getSession(): Session {
		return $this->session;
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