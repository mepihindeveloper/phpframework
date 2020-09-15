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
 * Класс UnknownErrorHttpException.
 * Класс исключений неизвестной ошибки.
 *
 * @package kernel\exception\http
 */
class UnknownErrorHttpException extends HttpException {
	
	/**
	 * @inheritdoc
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(500, $message, $code, $previous);
	}
}