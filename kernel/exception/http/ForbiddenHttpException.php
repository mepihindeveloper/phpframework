<?php
declare(strict_types = 1);

namespace kernel\exception;

use Throwable;

class ForbiddenHttpException extends HttpException {
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct(403, $message, $code, $previous);
	}
}