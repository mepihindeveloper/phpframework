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

class NotFoundHttpException extends HttpException {
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(404, $message, $code, $previous);
	}
}