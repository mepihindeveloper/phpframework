<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\validators;

use kernel\exception\EmailValidatorException;
use kernel\helpers\Email;

/**
 * Класс для работы с валидатором email адресов.
 * Класс реализует метод проверки email адреса.
 * Если в конфигурации присутствует шаблон для электронных адресов, то он применяется. Иначе возвращается true
 * как следствие пропуска проверки
 *
 * @package kernel\validators
 */
class EmailValidator extends Validator {
	
	/**
	 * @var Email
	 */
	private Email $email;
	
	/**
	 * @param Email $email Объект электронного адреса
	 */
	public function __construct(Email $email) {
		parent::__construct();
		
		$this->email = $email;
	}
	
	/**
	 * Проверяет корректность электронного адреса.
	 * Если в конфигурации присутствует шаблон для электронных адресов, то он применяется. Иначе возвращается true
	 * как следствие пропуска проверки.
	 *
	 * @throws EmailValidatorException
	 */
	public function validate(): void {
		if (!filter_var($this->email->getEmail(), FILTER_VALIDATE_EMAIL)) {
			throw new EmailValidatorException("Введенный электронный адрес {$this->email->getEmail()} не прошел проверку.");
		}
	}
}