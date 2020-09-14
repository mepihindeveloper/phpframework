<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use Exception;
use kernel\Application;
use kernel\exception\CsrfSecurityException;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\SessionErrorHttpException;
use kernel\KernelRegistry;
use kernel\pattern\Singleton;

/**
 * Класс для работы с Csrf токеном.
 * Класс предназначен для работы с безопасностью при Csrf атаках.
 *
 * @package kernel\helpers
 */
class Csrf extends Singleton {
	
	/**
	 * @var Session Объект сессии
	 */
	private Session $session;
	/**
	 * @var string Время жизни Csrf токена
	 */
	private string $lifeTime;
	/**
	 * @var array Настройки профиля Csrf
	 */
	private array $settings;
	
	public function __construct() {
		parent::__construct();
		$this->settings = Application::getInstance()->getConfig()->getProfileSection('security', 'csrf');
		$this->session = Application::getInstance()->getSession();
		$this->lifeTime = $this->settings['lifeTime'];
	}
	
	/**
	 * Назначает токен
	 *
	 * @throws SessionErrorHttpException
	 */
	public function set(): void {
		if ($this->settings['enable']) {
			if ((!$this->session->hasKey('csrf')) || $this->isExpired()) {
				$this->session->set('csrf', $this->getCreatedToken());
				$this->session->set('csrfStartTime', time());
			}
		} else {
			$this->remove();
		}
	}
	
	/**
	 * Проверяет токен на актуальность
	 *
	 * @return bool
	 * @throws SessionErrorHttpException
	 */
	private function isExpired(): bool {
		return $this->lifeTime === '0' ? false : time() > ($this->session->get('csrfStartTime') + (int)$this->lifeTime);
	}
	
	/**
	 * Создает токен
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getCreatedToken(): string {
		return bin2hex(random_bytes(32));
	}
	
	/**
	 * Проверяет активность режима работы с Csrf
	 *
	 * @throws CsrfSecurityException
	 */
	private function checkEnabled(): void {
		if (!$this->settings['enable']) {
			$this->remove();
			throw new CsrfSecurityException('Csrf модуль не влючен');
		}
	}
	
	private function remove(): void {
		if (!$this->settings['enable'] && $this->session->hasKey('csrf')) {
			$this->session->delete('csrf');
			$this->session->delete('csrfStartTime');
		}
	}
	
	/**
	 * Возвращает токен
	 *
	 * @return string
	 * @throws CsrfSecurityException
	 * @throws SessionErrorHttpException
	 */
	public function get(): string {
		$this->checkEnabled();
		
		return $this->session->get('csrf');
	}
	
	/**
	 * Проверяет валидность CSRF токена.
	 * В случае использования useCookies в настройках csrf токен берется из $_COOKIES и не требует передачи атрибута в
	 * метод.
	 *
	 * @param string $clientToken Токен клиента
	 *
	 * @return bool
	 * @throws CsrfSecurityException
	 * @throws SessionErrorHttpException
	 * @throws InvalidDataHttpException
	 */
	public function validate(string $clientToken = ''): bool {
		if ($this->settings['useCookies']) {
			/** @var Cookies $cookies */
			$cookies = KernelRegistry::getInstance()->get('cookies');
			
			return $cookies->has('csrf') ? hash_equals($this->get(), $cookies->get('csrf')) : false;
		}
		
		return hash_equals($this->get(), $clientToken);
	}
}