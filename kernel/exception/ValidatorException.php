<?php
declare(strict_types = 1);

namespace kernel\exception;

use Exception;
use Throwable;

class ValidatorException extends Exception {
	
	public $statusCode;
	
	public function __construct($statusCode, $message = "", $code = 0, Throwable $previous = null) {
		$this->statusCode = $statusCode;
		
		parent::__construct($message, $code, $previous);
	}
}