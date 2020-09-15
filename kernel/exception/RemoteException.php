<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\exception;

use Exception;
use Throwable;

/**
 * Класс RemoteException.
 * Родительский класс исключений при работе с сокетами и удаленными соединениями.
 *
 * @package kernel\exception
 */
class RemoteException extends Exception {
	
	/**
	 * @var int Код исключения
	 */
	public int $statusCode;
	
	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link https://php.net/manual/en/exception.construct.php
	 *
	 * @param $statusCode
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 */
	public function __construct($statusCode, $message = "", $code = 0, Throwable $previous = null) {
		$this->statusCode = $statusCode;
		
		parent::__construct($message, $code, $previous);
	}
}