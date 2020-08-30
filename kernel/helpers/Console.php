<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use const STDIN;
use const STDOUT;

/**
 * Касс-помощник для работы с консолью.
 * Класс реализует функции для работы с выводом и вводом при запуске CLI скриптов.
 *
 * @package kernel\helpers
 */
class Console {
	
	const FG_WHITE = 0;
	const FG_RED = 31;
	const FG_GREEN = 32;
	const FONT_NORMAL = 0;
	const FONT_BOLD = 1;
	
	/**
	 * Очищает экран консоли
	 */
	public static function clearScreen() {
		echo "\033[2J";
	}
	
	/**
	 * Записывает строку с переносом
	 *
	 * @param string|null $string Строка для записи
	 * @param int $color Цвет шрифта
	 *
	 * @return false|int
	 */
	public static function writeLine(string $string = null, int $color = self::FG_WHITE) {
		return static::write($string . PHP_EOL, $color);
	}
	
	/**
	 * Записывет строку без переноса
	 *
	 * @param string $string Строка для записи
	 * @param int $color Цвет шрифта
	 *
	 * @return false|int
	 */
	public static function write(string $string, int $color = self::FG_WHITE) {
		$resultString = "\033[0;{$color}m{$string}\033[0m";
		
		return fwrite(STDOUT, $resultString);
	}
	
	/**
	 * Записывает строку ошибки с переносом
	 *
	 * @param string|null $string Строка для записи
	 *
	 * @return false|int
	 */
	public static function writeLineError(string $string = null) {
		return static::writeError($string . PHP_EOL);
	}
	
	/**
	 * Записывает строку ошибки без переноса
	 *
	 * @param string $string Строка для записи
	 *
	 * @return false|int
	 */
	public static function writeError(string $string) {
		return static::write($string, self::FG_RED);
	}
	
	/**
	 * Выводит сообщение о подтверждении действия
	 *
	 * @param string $message Сообщение вопроса
	 * @param bool $default Значение по умолчанию
	 *
	 * @return bool
	 */
	public static function confirm(string $message, bool $default = false): bool {
		while (true) {
			static::write($message . ' (y|n) [' . ($default ? 'y' : 'n') . ']: ');
			$input = strtolower(trim(static::readLine()));
			
			if (empty($input))
				return $default;
			
			if (!strcasecmp($input, 'y'))
				return true;
			
			if (!strcasecmp($input, 'n'))
				return false;
		}
	}
	
	/**
	 * Получает введенную строку
	 *
	 * @return string
	 */
	public static function readLine(): string {
		return rtrim(fgets(STDIN), PHP_EOL);
	}
}