<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\validators;

use kernel\exception\validator\EmailValidatorException;
use kernel\exception\validator\MailValidatorException;
use kernel\helpers\Email;

/**
 * Класс для работы с валидатором почтовых писем.
 * Класс реализует методы проверки темы письма и отправителя.
 *
 * @package kernel\validators
 */
class MailValidator extends Validator {
	
	/**
	 * Проверяет тему письма на валидность
	 *
	 * @param string $subject Тема письма
	 *
	 * @throws MailValidatorException
	 */
	public function validateSubject(string $subject): void {
		$subjectLength = $this->settings['mail']['subjectLength'] ?? null;
		$isSubjectLengthCorrect = !(mb_strlen($subject) > $subjectLength);
		
		if (isset($isSubjectLengthCorrect) && !is_null($subjectLength) && !$isSubjectLengthCorrect) {
			throw new MailValidatorException('Длина темы превысила допустимые нормы.');
		}
	}
	
	/**
	 * Проверяет email на валидность
	 *
	 * @param array $sender Данные отправителя ['name', 'email']
	 *
	 * @throws MailValidatorException
	 * @throws EmailValidatorException
	 */
	public function validateSender(array $sender): void {
		if (empty($sender['email'])) {
			throw new MailValidatorException('Отсутствует email отправителя');
		}
		
		(new Email($sender['email']))->validate();
	}
}