<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\exception\validator\EmailValidatorException;
use kernel\validators\EmailValidator;

/**
 * Класс-помощник для работы с отдельными Email адресами.
 * Класс реализует работу с Email адресами в области получения и валидирования информации
 *
 * @package kernel\helpers
 */
class Email {
	
	/**
	 * @var string Электронный адрес
	 */
	private string $email;
	
	/**
	 * Email constructor.
	 *
	 * @param string $email Электронный адрес
	 */
	public function __construct(string $email) {
		$this->email = $email;
	}
	
	/**
	 * Получает имя (логин) электронного адреса
	 *
	 * @return string
	 * @throws EmailValidatorException
	 */
	public function getName(): string {
		$this->validate();
		
		return explode('@', $this->email)[0];
	}
	
	/**
	 * Проверяет корректность электронного адреса
	 *
	 * @return void
	 *
	 * @throws EmailValidatorException
	 */
	public function validate(): void {
		(new EmailValidator($this))->validate();
	}
	
	/**
	 * Получает доменное имя (хост) электронного адреса
	 *
	 * @return string
	 * @throws EmailValidatorException
	 */
	public function getDomain(): string {
		$this->validate();
		
		return explode('@', $this->email)[1];
	}
	
	/**
	 * Получает электронный адрес
	 *
	 * @return string
	 */
	public function getEmail(): string {
		return $this->email;
	}
}