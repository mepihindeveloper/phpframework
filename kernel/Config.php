<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel;

use kernel\exception\http\ServerErrorHttpException;
use kernel\pattern\Singleton;

/**
 * Класс для работы с конфигурацией.
 * Класс предназначен для работы с конфигурацией (настройками).
 *
 * @package kernel
 */
class Config extends Singleton {
	
	/**
	 * @var array Массив конфигрурации приложения
	 */
	private array $config;
	
	/**
	 * @throws ServerErrorHttpException
	 */
	protected function __construct() {
		parent::__construct();
		
		$configFile = ROOT . 'configs/config.php';
		if (!file_exists($configFile)) {
			throw new ServerErrorHttpException('Отсутствует файл конфигурации');
		}
		
		$this->config = require $configFile;
	}
	
	/**
	 * Получение конфигурации приложения
	 *
	 * @return array
	 */
	public function getConfig(): array {
		return $this->config;
	}
	
	/**
	 * Получает настройки секции конфигурации
	 *
	 * @param string $section Секция конфигурации
	 * @param string $profile Профиль конфигурации. Значение по умолчанию <b>active</b> говорит о том, что берутся
	 * высталвенная пользователем секция по умолчанию.
	 *
	 * @return array
	 *
	 * @throws ServerErrorHttpException
	 */
	public function getProfileSection(string $section, string $profile = 'active'): array {
		if (!$this->has($section, $profile)) {
			throw new ServerErrorHttpException("Отсутствует секция {$section} и/или профиль {$profile}");
		}
		
		return $this->config[$section][$profile];
	}
	
	/**
	 * Проверяет наличие секции и/или профиля в конфигурации
	 *
	 * @param string $section Секция конфигурации
	 * @param string|null $profile Профиль конфигурации. Если указано значение <b>null</b>, то проверяется толькое
	 * наличие секции
	 *
	 * @return bool
	 */
	public function has(string $section, string $profile = null): bool {
		return is_null($profile) ?
			array_key_exists($section, $this->config) && isset($this->config[$section]) :
			array_key_exists($section, $this->config) && isset($this->config[$section][$profile]);
	}
	
	/**
	 * Получает настройки активного профиля
	 *
	 * @param string $section Секция конфигурации
	 *
	 * @return array
	 *
	 * @throws ServerErrorHttpException
	 */
	public function getActiveSettings(string $section): array {
		$sectionSettings = $this->getSection($section);
		
		return $sectionSettings[$sectionSettings['active']];
	}
	
	/**
	 * Получает настройки секции
	 *
	 * @param string $section Секция конфигурации
	 *
	 * @return mixed
	 *
	 * @throws ServerErrorHttpException
	 */
	public function getSection(string $section) {
		if (!$this->has($section)) {
			throw new ServerErrorHttpException("Отсутствует секция {$section}");
		}
		
		return $this->config[$section];
	}
}