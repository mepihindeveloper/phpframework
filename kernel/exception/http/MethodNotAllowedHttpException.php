<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\exception\http;

use kernel\exception\HttpException;
use Throwable;

/**
 * Класс MethodNotAllowedHttpException.
 * Класс исключений при условии отсутствия вызываемого метода.
 *
 * @package kernel\exception\http
 */
class MethodNotAllowedHttpException extends HttpException {
	
	/**
	 * @inheritdoc
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(405, $message, $code, $previous);
	}
}