<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Application;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;
use kernel\exception\SessionErrorHttpException;
use kernel\pattern\Singleton;

/**
 * Класс-помощник для работы с сессиями.
 * Класс реализует открытие и закрытие сессии, получение, добавление и удаление ключей сессии. Реализует проверки на
 * наличие ключей, срок жизни сессии.
 *
 * В классе реализована логика срока жизни, которая связана с Cookies
 *
 * @package kernel\helpers
 * @see Cookies
 */
class Session extends Singleton {
	
	/**
	 * @var string Имя сессии
	 */
	private string $name;
	/**
	 * @var array Активные настройки
	 */
	private array $activeSettings;
	/**
	 * @var string Время жизни сессии при бездействии в секундах
	 */
	private string $inactionLifeTime;
	/**
	 * @var string Время жизни идентификатора сессии в секундах
	 */
	private string $idLifeTime;
	/**
	 * @var string Время жизни сессии в секундах
	 */
	private string $lifeTime;
	/**
	 * @var Application Объект класса приложения
	 */
	private Application $application;
	
	/**
	 * Создает ключ активности сессии пользователя
	 *
	 * @throws SessionErrorHttpException
	 */
	public function beginActivity(): void {
		if (!$this->hasKey('activity')) {
			$this->set('activity', ['startTime' => time()]);
		}
	}
	
	/**
	 * Удаляет ключ activity пользователя
	 *
	 * @throws SessionErrorHttpException
	 */
	public function endActivity(): void {
		$this->delete('activity');
	}
	
	/**
	 * Запускает сессиию
	 *
	 * @param string $name
	 *
	 * @return void
	 *
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 * @throws SessionErrorHttpException
	 */
	public function start(string $name = 'appsession') {
		$this->name = $name;
		$this->application = Application::getInstance();
		$this->activeSettings = $this->application->getConfig()->getActiveSettings('session');
		
		$this->init();
		
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена');
		}
		
		if ($this->hasKey('activity')) {
			if ($this->isIdExpired() || $this->isExpired()) {
				$this->refresh();
			}
			
			if (!$this->isValid()) {
				$this->endActivity();
			}
		}
	}
	
	/**
	 * Инициализирует параметры сессии.
	 * Указывается, что сеансы должны передаваться только с помощью файлов cookie,
	 * исключая возможность отправки идентификатора сеанса в качестве параметра «GET».
	 * Установка параметров cookie идентификатора сеанса. Эти параметры могут быть переопределены при инициализации
	 * обработчика сеанса, однако рекомендуется использовать значения по умолчанию, разрешающие отправку
	 * только по HTTPS (если имеется) и ограниченный доступ HTTP (без доступа к сценарию на стороне клиента).
	 *
	 * @return void
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 */
	private function init(): void {
		$this->lifeTime = $this->activeSettings['lifeTime'];
		$this->idLifeTime = $this->activeSettings['idLifeTime'];
		$this->inactionLifeTime = $this->activeSettings['inactionLifeTime'];
		
		ini_set('session.gc_maxlifetime', $this->lifeTime);
		$this->setName($this->name);
		
		if (array_key_exists('cookies', $this->activeSettings)) {
			// Определяет, будет ли модуль использовать cookies для хранения идентификатора сессии на стороне клиента
			if (array_key_exists('use_cookies', $this->activeSettings)) {
				ini_set('session.use_cookies', $this->activeSettings['use_cookies']);
			}
			// Определяет, будет ли модуль использовать только cookies для хранения идентификатора сессии на стороне клиента
			if (array_key_exists('use_only_cookies', $this->activeSettings)) {
				ini_set('session.use_only_cookies', $this->activeSettings['use_only_cookies']);
			}
			
			$sessionCookiesSettings = $this->application->getConfig()->getProfileSection('cookies', 'session');
			
			session_set_cookie_params(
				$sessionCookiesSettings['expire'],
				$sessionCookiesSettings['path'],
				$sessionCookiesSettings['domain'],
				$sessionCookiesSettings['secure'],
				$sessionCookiesSettings['httponly']
			);
		}
		
		session_start();
	}
	
	/**
	 * Проверяет активность сессии
	 *
	 * @return bool
	 */
	public function isActive(): bool {
		return session_status() === PHP_SESSION_ACTIVE;
	}
	
	/**
	 * Проверяет наличие ключа (переменной) сессии
	 *
	 * @param string $key Ключ (переменная) сессии
	 *
	 * @param string $section Секция, в которой необходимо произвести поиск
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function hasKey(string $key, string $section = ''): bool {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return empty($section) ? array_key_exists($key, $_SESSION) : array_key_exists($key, $_SESSION[$section]);
	}
	
	/**
	 * Устанавливает ключ (переменную) и значение сессии
	 *
	 * @param string $key Ключ (переменная) сессии
	 * @param mixed $value Устанавливаемое значение
	 * @param string $section Секция, в которую необходимо записать данные
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 */
	public function set(string $key, $value, string $section = ''): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		if (empty($section)) {
			$_SESSION[$key] = $value;
		} else {
			$_SESSION[$section][$key] = $value;
		}
	}
	
	/**
	 * Проверяет срок действия идентификатора сессии
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function isIdExpired(): bool {
		if ($this->idLifeTime === '0')
			return false;
		
		$refreshLastTime = $this->hasKey('refreshLastTime', 'activity') ? $this->get('refreshLastTime', 'activity') : false;
		
		return (!$refreshLastTime || (time() - $refreshLastTime) > $this->idLifeTime);
	}
	
	/**
	 * Получает значение сессии по ключу
	 *
	 * @param string $key Ключ (переменная) сессии
	 * @param string $section Секция, из которой необходимо взять данные
	 *
	 * @return mixed
	 *
	 * @throws SessionErrorHttpException
	 */
	public function get(string $key, string $section = '') {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return empty($section) ? $_SESSION[$key] : $_SESSION[$section][$key];
	}
	
	/**
	 * Перезагружает (повторная генерация и установка значения) параметров сессии
	 *
	 * @param bool $needDeleteSession
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 * @see Session::refreshFingerprint()
	 *
	 */
	public function refresh(bool $needDeleteSession = false): void {
		session_regenerate_id($needDeleteSession);
		$this->set('refreshLastTime', time(), 'activity');
		$this->refreshFingerprint();
	}
	
	/**
	 * Перезагружает (повторная генерация и установка значения) "отпечаток" клиента
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 */
	public function refreshFingerprint(): void {
		$this->set('activity', $this->generateHash(), 'activity');
	}
	
	/**
	 * Генерирует хеш из HTTP_USER_AGENT, текущей даты в формате Y-m-d, идентификатора сессии и ip адреса
	 *
	 * @return string
	 */
	public function generateHash(): string {
		return sha1(
			$_SERVER['HTTP_USER_AGENT'] . date('Y-m-d') . $this->getId() .
			(ip2long($_SERVER['REMOTE_ADDR']) & ip2long('255.255.0.0'))
		);
	}
	
	/**
	 * Получает идентификатор сессии
	 *
	 * @return string
	 */
	public function getId(): string {
		return session_id();
	}
	
	/**
	 * Проверяет валидность сессии
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 * @see  Session::isFingerprint()
	 * @see  Session::isExpired()
	 *
	 */
	public function isValid(): bool {
		return $this->isFingerprint() && !$this->isExpired();
	}
	
	/**
	 * Проверяет "отпечаток" клиента
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function isFingerprint(): bool {
		$hash = $this->generateHash();
		
		if ($this->hasKey('fingerprint', 'activity')) {
			return $hash === $this->get('fingerprint', 'activity');
		}
		
		$this->set('fingerprint', $hash, 'activity');
		
		return true;
	}
	
	/**
	 * Проверяет срок действия сесии
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function isExpired(): bool {
		$lastActivity = $this->hasKey('lastActivity', 'activity') ? $this->get('lastActivity', 'activity') : false;
		$startTime = $this->hasKey('startTime', 'activity') ? $this->get('startTime', 'activity') : false;
		$time = time();
		$isLifeExpired = ($time - $startTime) > (int)$this->lifeTime;
		$isInactionExpired = $lastActivity && ($time - $lastActivity) > (int)$this->inactionLifeTime;
		
		if ($this->lifeTime !== '0' && $isLifeExpired) {
			return true;
		}
		
		if ($this->inactionLifeTime !== '0' && $isInactionExpired) {
			return true;
		}
		
		$this->set('lastActivity', $time, 'activity');
		
		return false;
	}
	
	/**
	 * Закрывает сессиию
	 *
	 * @return void
	 *
	 * @throws InvalidDataHttpException
	 * @throws SessionErrorHttpException
	 * @see Session::deleteAll()
	 */
	public function close(): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		$this->deleteAll();
		
		if (array_key_exists('cookies', $this->activeSettings)) {
			$this->application->getCookies()->remove($this->getName());
		}
		
		session_destroy();
	}
	
	/**
	 * Удаляет все переменные сессии
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 */
	public function deleteAll(): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		session_unset();
	}
	
	/**
	 * Получает имя сессии
	 *
	 * @return string
	 *
	 * @throws SessionErrorHttpException
	 */
	public function getName() {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return session_name();
	}
	
	/**
	 * Устанавливает имя сессии
	 *
	 * @param string $name Имя сессии
	 *
	 * @return void
	 */
	public function setName(string $name): void {
		session_name($name);
	}
	
	/**
	 * Устанавливает идентификатор сессии
	 *
	 * @param string $id
	 *
	 * @return void
	 */
	public function setId(string $id): void {
		session_id($id);
	}
	
	/**
	 * Получает массив $_SESSION
	 *
	 * @return array
	 *
	 * @throws SessionErrorHttpException
	 */
	public function getAll(): array {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return $_SESSION;
	}
	
	/**
	 * Удаляет ключ из сессии
	 *
	 * @param string $key
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 */
	public function delete(string $key): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		unset($_SESSION[$key]);
	}
}