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
use kernel\exception\http\FileErrorHttpException;
use kernel\exception\http\InvalidDataHttpException;
use kernel\exception\http\ServerErrorHttpException;
use kernel\KernelRegistry;
use kernel\pattern\Singleton;

/**
 * Класс-помощник для работы с запросами.
 * Класс реализует возможность управлять запросами из внешней среды.
 *
 * @package kernel\helpers
 */
class Request extends Singleton {
	
	/**
	 * @var Headers Объект управления заголовками
	 */
	private Headers $headers;
	
	protected function __construct() {
		parent::__construct();
		$this->headers = Application::getInstance()->getHeaders();
	}
	
	/**
	 * Проверяет является ли запрос GET
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isGet(): bool {
		return $this->getRequestMethod() === 'GET';
	}
	
	/**
	 * Получает метод запроса (GET, POST, HEAD, PUT, PATCH, DELETE)
	 *
	 * @return string
	 * @throws InvalidDataHttpException
	 */
	public function getRequestMethod(): string {
		if ($this->headers->has('X-Http-Method-Override')) {
			return strtoupper($this->headers->get('X-Http-Method-Override'));
		}
		
		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	}
	
	/**
	 * Проверяет является ли запрос POST
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isPost(): bool {
		return $this->getRequestMethod() === 'POST';
	}
	
	/**
	 * Проверяет является ли запрос Ajax
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isAjax(): bool {
		return $this->headers->get('X-Requested-With') === 'XMLHttpRequest';
	}
	
	/**
	 * Проверяет является ли запрос PUT
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isPut(): bool {
		return $this->getRequestMethod() === 'PUT';
	}
	
	/**
	 * Проверяет является ли запрос DELETE
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isDelete(): bool {
		return $this->getRequestMethod() === 'DELETE';
	}
	
	/**
	 * Проверяет является ли запрос PATCH
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isPatch(): bool {
		return $this->getRequestMethod() === 'PATCH';
	}
	
	/**
	 * Проверяет является ли запрос OPTIONS
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isOptions(): bool {
		return $this->getRequestMethod() === 'OPTIONS';
	}
	
	/**
	 * Проверяет является ли запрос HEAD
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function isHead(): bool {
		return $this->getRequestMethod() === 'HEAD';
	}
	
	/**
	 * Получает параметр GET с заданным именем. Если имя не указано, Получает массив всех параметров GET
	 *
	 * @param string $key Ключ
	 *
	 * @return mixed
	 */
	public function get(string $key = '') {
		return empty($key) ? $_GET : $_GET[$key];
	}
	
	/**
	 * Получает параметр POST с заданным именем. Если имя не указано, Получает массив всех параметров POST
	 *
	 * @param string $key Ключ
	 *
	 * @return mixed
	 */
	public function post(string $key = '') {
		return empty($key) ? $_POST : $_POST[$key];
	}
	
	/**
	 * Получает имя хоста другого конца этого соединения. Заголовки игнорируются
	 *
	 * @return string|null
	 */
	public function getRemoteHost(): ?string {
		return $_SERVER['REMOTE_HOST'] ?? null;
	}
	
	/**
	 * Получает IP на другом конце этого соединения. Заголовки игнорируются
	 *
	 * @return string|null
	 */
	public function getRemoteIP(): ?string {
		return $_SERVER['REMOTE_ADDR'] ?? null;
	}
	
	/**
	 * Получает user agent
	 *
	 * @return string
	 * @throws InvalidDataHttpException
	 */
	public function getUserAgent(): string {
		return $this->headers->get('User-Agent');
	}
	
	/**
	 * Получает URL-реферер
	 *
	 * @return string
	 * @throws InvalidDataHttpException
	 */
	public function getReferrer(): string {
		return $this->headers->get('Referer');
	}
	
	/**
	 * Получает имя сервера
	 *
	 * @return string
	 */
	public function getServerName(): string {
		return $_SERVER['SERVER_NAME'];
	}
	
	/**
	 * Получает тип контента запроса
	 *
	 * @return string
	 * @throws InvalidDataHttpException
	 */
	public function getContentType(): string {
		return $_SERVER['CONTENT_TYPE'] ?? $this->headers->get('Content-Type');
	}
	
	/**
	 * Получает часть хоста текущего запроса URL
	 *
	 * @return mixed
	 * @throws InvalidDataHttpException
	 */
	public function getHostName() {
		return parse_url($this->getHostInfo(), PHP_URL_HOST);
	}
	
	/**
	 * Получает схему и часть хоста текущего запроса URL. Возвращенный URL не имеет конечной косой черты.
	 *
	 * @return string|null
	 * @throws InvalidDataHttpException
	 */
	public function getHostInfo(): ?string {
		$isSecure = $this->isSecureConnection();
		$protocol = $isSecure ? 'https' : 'http';
		$hostInfo = null;
		
		if ($this->headers->has('X-Forwarded-Host')) {
			$hostInfo = "{$protocol}://" . trim(explode(',', $this->headers->get('X-Forwarded-Host'))[0]);
		} else if ($this->headers->has('Host')) {
			$hostInfo = "{$protocol}://" . $this->headers->get('Host');
		} else if (isset($_SERVER['SERVER_NAME'])) {
			$hostInfo = "{$protocol}://" . $_SERVER['SERVER_NAME'];
			$port = $isSecure ? $this->getSecurePort() : $this->getPort();
			
			if (($port !== 80 && !$isSecure) || ($port !== 443 && $isSecure)) {
				$hostInfo .= ":{$port}";
			}
		}
		
		return $hostInfo;
	}
	
	/**
	 * Проверяет наличие протокола защищенного соединения
	 *
	 * @return bool
	 */
	public function isSecureConnection(): bool {
		return (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] === 1));
	}
	
	/**
	 * Получает порт защищенного соединения
	 *
	 * @return int
	 */
	public function getSecurePort(): int {
		$serverPort = $this->getServerPort();
		
		return !$this->isSecureConnection() && $serverPort !== null ? $serverPort : 443;
	}
	
	/**
	 * Получает порт соединения
	 *
	 * @return int|null
	 */
	public function getServerPort(): ?int {
		return $_SERVER['SERVER_PORT'] ?? null;
	}
	
	/**
	 * Получает порт, используемый для небезопасных запросов.
	 * По умолчанию 80, или порт, указанный сервером, если текущий запрос небезопасен
	 *
	 * @return int
	 */
	public function getPort(): int {
		$serverPort = $this->getServerPort();
		
		return !$this->isSecureConnection() && $serverPort !== null ? $serverPort : 80;
	}
	
	/**
	 * Получает относительный URL-адрес сценария входа
	 *
	 * @return string
	 *
	 * @throws FileErrorHttpException
	 * @throws ServerErrorHttpException
	 */
	public function getScriptUrl(): string {
		$scriptFile = $this->getScriptFile();
		$scriptName = basename($scriptFile);
		$scriptUrl = '';
		
		if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
			$scriptUrl = $_SERVER['SCRIPT_NAME'];
		} else if (isset($_SERVER['PHP_SELF'])) {
			if (basename($_SERVER['PHP_SELF']) === $scriptName) {
				$scriptUrl = $_SERVER['PHP_SELF'];
			} else if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
				$scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
			}
		} else if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
			$scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
		} else if (!empty($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
			$scriptUrl = str_replace([$_SERVER['DOCUMENT_ROOT'], '\\'], ['', '/'], $scriptFile);
		} else {
			throw new ServerErrorHttpException('Невозможно определить URL сценария входа.');
		}
		
		return $scriptUrl;
	}
	
	/**
	 * Получает относительный URL-адрес сценария входа
	 *
	 * @return string
	 *
	 * @throws FileErrorHttpException
	 */
	public function getScriptFile(): string {
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			return $_SERVER['SCRIPT_FILENAME'];
		}
		
		throw new FileErrorHttpException('Невозможно определить путь к файлу сценария входа');
	}
	
	/**
	 * Полуачет часть URL запроса, которая находится после знака вопроса
	 *
	 * @return string
	 */
	public function getQueryString(): string {
		return $_SERVER['QUERY_STRING'] ?? '';
	}
	
	/**
	 * Проверяет валидность CSRF токена. Валидация происходит только для non-"safe" методов.
	 * В случае использования useCookies в настройках csrf токен берется из $_COOKIES и не требует передачи атрибута в
	 * метод.
	 *
	 * @see https://tools.ietf.org/html/rfc2616#section-9.1.1
	 *
	 * @param string $clientToken Токен клиента
	 *
	 * @return bool
	 * @throws InvalidDataHttpException
	 */
	public function validateCsrfToken(string $clientToken = ''): bool {
		if (in_array($this->getRequestMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
			return true;
		}
		
		return KernelRegistry::getInstance()->get('csrf')->validate($clientToken);
	}
	
	/**
	 * Получает CSRF токен из сессии
	 *
	 * @return mixed
	 * @throws InvalidDataHttpException
	 */
	public function getCsrfToken() {
		return KernelRegistry::getInstance()->get('csrf')->get();
	}
}