<?php
declare(strict_types = 1);

namespace kernel\validators;

use kernel\Config;

/**
 * Класс для работы с валидаторами данных.
 * Класс предназначен для работы с валидатороами данных. Выступает в качестве базового класса для всех валидаторов.
 * В базовом класссе получается конфигурация валидаторов из настроек.
 *
 * @package kernel\validators
 */
class Validator {
	
	/**
	 * @var array Конфигурация валидаторов
	 */
	protected array $settings;
	
	public function __construct() {
		$this->settings = Config::getInstance()->getSection('validators');
	}
}