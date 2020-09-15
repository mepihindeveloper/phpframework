<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\exception\security;

use kernel\exception\SecurityException;
use Throwable;

/**
 * Класс CsrfSecurityException.
 * Класс исключений при работе с CSRF токеном.
 *
 * @package kernel\exception\security
 */
class CsrfSecurityException extends SecurityException {
	
	/**
	 * @inheritdoc
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(500, $message, $code, $previous);
	}
}