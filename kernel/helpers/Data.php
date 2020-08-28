<?php
declare(strict_types = 1);

namespace kernel\helpers;

/**
 * Класс-помощник для работы с данными.
 * Класс реализует преобразование специальных символов и другую нормализацию данных.
 *
 * @package kernel\helpers
 */
class Data {
	
	/**
	 * Преобразует специальные символы в HTML-сущности
	 *
	 * @see https://www.php.net/manual/ru/function.htmlspecialchars.php htmlspecialchars
	 *
	 * @param string $data Конвертируемая строка
	 * @param int $specialCharsFlags Битовая маска
	 * @param string|null $encoding Кодировка при конвертации симоволов
	 *
	 * @return string
	 */
	public static function secureHtmlChars(string $data, int $specialCharsFlags = ENT_QUOTES | ENT_HTML5, string $encoding = null): string {
		$charset = $encoding ?? ini_get("default_charset");
		
		return htmlspecialchars($data, $specialCharsFlags, $charset);
	}
	
	/**
	 * Преобразует все возможные символы в соответствующие HTML-сущности
	 *
	 * @see https://www.php.net/manual/en/function.htmlentities.php htmlentities
	 *
	 * @param string $data Конвертируемая строка
	 * @param int $specialCharsFlags Битовая маска
	 * @param string|null $encoding Кодировка при конвертации симоволов
	 *
	 * @return string
	 */
	public static function secureHtmlEntities(string $data, int $specialCharsFlags = ENT_QUOTES | ENT_HTML5, string $encoding = null): string {
		$charset = $encoding ?? ini_get("default_charset");
		
		return htmlentities($data, $specialCharsFlags, $charset);
	}
	
	/**
	 * Удаляет лишние пробелы в строке
	 *
	 * @param string $data Преобразуемая строка
	 *
	 * @return mixed
	 */
	public static function normalizeWhitespace(string $data) {
		return self::removeDuplicateWhitespace(self::trim($data));
	}
	
	/**
	 * Удаляет более 2-х пробелов
	 *
	 * @param string $data Преобразуемая строка
	 *
	 * @return mixed
	 */
	public static function removeDuplicateWhitespace(string $data) {
		return preg_replace("/\s{2,}/", " ", $data);
	}
	
	/**
	 * Удаляет пробелы (или другие символы) из начала и конца строки
	 *
	 * @see https://www.php.net/manual/ru/function.trim.php trim
	 *
	 * @param string $data Обрезаемая строка
	 * @param string|null $charset Список символов для удаления
	 *
	 * @return string
	 */
	public static function trim(string $data, string $charset = null): string {
		return is_null($charset) ? trim($data) : trim($data, $charset);
	}
}