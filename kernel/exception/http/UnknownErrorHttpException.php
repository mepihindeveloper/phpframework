<?php
declare(strict_types = 1);

namespace kernel\exception;

use Throwable;

class UnknownErrorHttpException extends HttpException {
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(500, $message, $code, $previous);
	}
}