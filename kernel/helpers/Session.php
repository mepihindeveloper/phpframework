<?php
declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Application;
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
	 * @var int Время жизни сессии при бездействии в секундах
	 */
	private int $inactionLifeTime;
	/**
	 * @var int Время жизни идентификатора сессии в секундах
	 */
	private int $idLifeTime;
	/**
	 * @var int Время жизни сессии в секундах
	 */
	private int $lifeTime;
	/**
	 * @var Application Объект класса приложения
	 */
	private Application $application;
	
	/**
	 * @param string $name Имя сессии
	 *
	 * @throws ServerErrorHttpException
	 */
	protected function __construct(string $name = 'appsession') {
		$this->name = $name;
		$this->application = Application::getInstance();
		$this->activeSettings = $this->application->getConfig()->getActiveSettings('session');
	}
	
	/**
	 * Запускает сессиию
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 * @see Session::refresh()
	 *
	 * @see Session::isIdExpired()
	 * @see Session::isValid()
	 */
	public function open(): void {
		$this->init();
		session_start();
		
		// Проверка на корректность инициализированнйо сессии
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена');
		}
		
		// Установка времени запуска сессии
		if (!$this->hasKey('startTime')) {
			$this->set('startTime', time());
		}
		
		if ($this->isIdExpired()) {
			$this->refresh();
		}
		
		if (!$this->isValid()) {
			$this->close();
		}
	}
	
	/**
	 * Инициализирует параметры сессии
	 *
	 * @return void
	 */
	private function init(): void {
		/*
		 * Указывается, что сеансы должны передаваться только с помощью файлов cookie,
		 * исключая возможность отправки идентификатора сеанса в качестве параметра «GET».
		 * Установка параметров cookie идентификатора сеанса. Эти параметры могут быть переопределены при инициализации
		 * обработчика сеанса, однако рекомендуется использовать значения по умолчанию, разрешающие отправку
		 * только по HTTPS (если имеется) и ограниченный доступ HTTP (без доступа к сценарию на стороне клиента).
		 */
		
		$this->lifeTime = $this->activeSettings['lifeTime'];
		$this->idLifeTime = $this->activeSettings['idLifeTime'];
		$this->inactionLifeTime = $this->activeSettings['inactionLifeTime'];
		
		// Определяет, будет ли модуль использовать cookies для хранения идентификатора сессии на стороне клиента
		ini_set('session.use_cookies', $this->activeSettings['use_cookies']);
		// Определяет, будет ли модуль использовать только cookies для хранения идентификатора сессии на стороне клиента
		ini_set('session.use_only_cookies', $this->activeSettings['use_only_cookies']);
		ini_set('session.gc_maxlifetime', $this->lifeTime);
		
		$this->setName($this->name);
		
		session_set_cookie_params(
			$this->application->getCookies()->get('lifetime'),
			$this->application->getCookies()->get('path'),
			$this->application->getCookies()->get('domain'),
			$this->application->getCookies()->get('secure'),
			$this->application->getCookies()->get('httponly')
		);
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
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function hasKey(string $key): bool {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return array_key_exists($key, $_SESSION);
	}
	
	/**
	 * Устанавливает ключ (переменную) и значение сессии
	 *
	 * @param string $key Ключ (переменная) сессии
	 * @param mixed $value Устанавливаемое значение
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 */
	public function set(string $key, $value): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		$_SESSION[$key] = $value;
	}
	
	/**
	 * Проверяет срок действия идентификатора сессии
	 *
	 * @return bool
	 *
	 * @throws SessionErrorHttpException
	 */
	public function isIdExpired(): bool {
		if ($this->idLifeTime === 0)
			return false;
		
		$refreshLastTime = $this->hasKey('refreshLastTime') ? $this->get('refreshLastTime') : false;
		
		return (!$refreshLastTime || (time() - $refreshLastTime) > $this->idLifeTime);
	}
	
	/**
	 * Получает значение сессии по ключу
	 *
	 * @param string $key Ключ (переменная) сессии
	 *
	 * @return mixed
	 *
	 * @throws SessionErrorHttpException
	 */
	public function get(string $key) {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		return $_SESSION[$key];
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
		$this->set('refreshLastTime', time());
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
		$this->set('fingerprint', $this->generateHash());
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
		
		if ($this->hasKey('fingerprint')) {
			return $hash === $this->get('fingerprint');
		}
		
		$this->set('fingerprint', $hash);
		
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
		$lastActivity = $this->hasKey('lastActivity') ? $this->get('lastActivity') : false;
		$startTime = $this->hasKey('startTime') ? $this->get('startTime') : false;
		$time = time();
		
		$isLifeExpired = ($time - $startTime) > $this->lifeTime;
		$isInactionExpired = $lastActivity && ($time - $lastActivity) > $this->inactionLifeTime;
		
		if ($this->inactionLifeTime !== 0 && $this->lifeTime !== 0 && ($isInactionExpired || $isLifeExpired)) {
			return true;
		}
		
		if (($this->lifeTime !== 0 && $isLifeExpired) || ($this->inactionLifeTime !== 0 && $isInactionExpired)) {
			return true;
		}
		
		$this->set('lastActivity', $time);
		
		return false;
	}
	
	/**
	 * Закрывает сессиию
	 *
	 * @return void
	 *
	 * @throws SessionErrorHttpException
	 * @see Session::deleteAll()
	 *
	 */
	public function close(): void {
		if (!$this->isActive()) {
			throw new SessionErrorHttpException('Ошибка в работе сессий. Сессия не запущена.');
		}
		
		$this->deleteAll();
		$this->application->getCookies()->remove($this->getName());
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