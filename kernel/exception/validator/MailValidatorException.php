<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\exception\validator;

use kernel\exception\ValidatorException;
use Throwable;

/**
 * Класс MailValidatorException.
 * Класс исключений валидации почтовых отправлений.
 *
 * @package kernel\exception\validator
 */
class MailValidatorException extends ValidatorException {
	
	/**
	 * @inheritdoc
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(500, $message, $code, $previous);
	}
}