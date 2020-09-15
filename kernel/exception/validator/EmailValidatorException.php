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
 * Класс EmailValidatorException.
 * Класс исключений валидации email адресов.
 *
 * @package kernel\exception\validator
 */
class EmailValidatorException extends ValidatorException {
	
	/**
	 * @inheritdoc
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(500, $message, $code, $previous);
	}
}