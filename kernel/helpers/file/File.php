<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers\file;

use kernel\Application;
use kernel\exception\FileErrorHttpException;
use kernel\exception\ForbiddenHttpException;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;

/**
 * Касс-помощник для работы с файлами.
 * Класс реализует функцинал создания, удаления, проверок файлов и другие операции.
 *
 * @package kernel\helpers\file
 */
class File {
	
	/**
	 * @var self
	 */
	protected File $file;
	/**
	 * @var string|null Путь к файлу
	 */
	protected ?string $path;
	/**
	 * @var array Настройки активного профиля
	 */
	private array $settings;
	
	/**
	 * File constructor.
	 *
	 * @param string $path Путь к файлу
	 *
	 * @throws ServerErrorHttpException
	 */
	public function __construct(string $path) {
		$application = Application::getInstance();
		$this->settings = $application->getConfig()->getActiveSettings('files');
		$this->file = $this;
		$this->path = $path;
	}
	
	/**
	 * Генерирует имя файла
	 *
	 * @see https://www.php.net/manual/ru/function.sha1.php Возвращает SHA1-хеш строки
	 * @see https://www.php.net/manual/ru/function.microtime.php Возвращает текущую метку времени Unix с
	 *     микросекундами
	 *
	 * @return string
	 */
	public static function generateFilename(): string {
		return sha1(microtime() . rand(0, 9999));
	}
	
	/**
	 * Создает файл
	 *
	 * @param bool $removeRestriction Требование к удалению ограничений к расширению файлу.
	 *
	 * @throws InvalidDataHttpException
	 */
	public function create(bool $removeRestriction = false): void {
		$extension = pathinfo($this->path)['extension'];
		
		if (!$removeRestriction && !$this->isAllowedExtension($extension)) {
			throw new InvalidDataHttpException("Недопустимое расширение файла {$extension}");
		}
		
		file_put_contents($this->path, null, LOCK_EX);
	}
	
	/**
	 * Проверяет расширение файла на допустимость
	 *
	 * @param string $extension Расширение
	 *
	 * @return bool
	 */
	public function isAllowedExtension(string $extension): bool {
		return in_array(strtolower($extension), array_keys($this->settings['formats']));
	}
	
	/**
	 * Получает путь до директории файла
	 *
	 * @return string|null
	 */
	public function getFileDirectory(): ?string {
		$path = pathinfo($this->path, PATHINFO_DIRNAME);
		
		return $path === '.' ? null : $path;
	}
	
	/**
	 * Получает имя файла с раширением или без расширения
	 *
	 * @param bool $needExtension Параметр, указывающий необходимость получения расширения файла
	 *
	 * @return string
	 */
	public function getFileName($needExtension = false): string {
		return pathinfo($this->path, $needExtension ? PATHINFO_BASENAME : PATHINFO_FILENAME);
	}
	
	/**
	 * Получает путь до файла
	 *
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}
	
	/**
	 * Перезаписывает файл
	 *
	 * @see https://www.php.net/manual/ru/function.file-put-contents.php file_put_contents
	 *
	 * @param string $data Содержимое
	 * @param int $flags Флаги перезаписи
	 *
	 * @return void
	 */
	public function rewrite(string $data, int $flags = LOCK_EX): void {
		file_put_contents($this->path, $data, $flags);
	}
	
	/**
	 * Дополняет файл содержимым
	 *
	 * @see https://www.php.net/manual/ru/function.file-put-contents.php file_put_contents
	 *
	 * @param string $data Содержимое
	 * @param int $flags Флаги перезаписи
	 *
	 * @return void
	 */
	public function writeAppend(string $data, int $flags = LOCK_EX | FILE_APPEND): void {
		file_put_contents($this->path, $data, $flags);
	}
	
	/**
	 * Удаляет файл
	 *
	 * @return void
	 *
	 * @throws FileErrorHttpException
	 */
	public function delete(): void {
		if (!file_exists($this->path)) {
			throw new FileErrorHttpException("Не возможно удалить файл, так как он не существует: {$this->path}");
		}
		
		unlink($this->path);
		$this->path = null;
	}
	
	/**
	 * Получает содержимое файла
	 *
	 * @see https://www.php.net/manual/ru/function.file-get-contents.php file_get_contents
	 *
	 * @param bool $useIncludePath Рекурсивный обход папок при поиске файла
	 * @param null $context Корректный ресурс контекста
	 * @param int $offset Смещение, с которого начнется чтение оригинального потока
	 * @param int $maxlength Максимальный размер читаемых данных
	 *
	 * @return string|null
	 *
	 * @throws FileErrorHttpException
	 */
	public function getContent(bool $useIncludePath = false, $context = null, int $offset = 0, int $maxlength = 0): ?string {
		if (!file_exists($this->path)) {
			throw new FileErrorHttpException("Не возможно получить содержимое файла, так как он не существует: {$this->path}");
		}
		
		$maxlength = $maxlength === 0 ? filesize($this->path) : $maxlength;
		
		return file_get_contents($this->path, $useIncludePath, $context, $offset, $maxlength);
	}
	
	/**
	 * Переименовывает файл
	 *
	 * @param string $name Новое имя
	 *
	 * @return void
	 */
	public function rename(string $name): void {
		rename($this->path, dirname($this->path) . "/{$name}");
	}
	
	/**
	 * Получает настройки формата файла
	 *
	 * @return array
	 */
	public function getFormatSettings(): array {
		return $this->settings['formats'][$this->getExtension()];
	}
	
	/**
	 * Получает расширение файла
	 *
	 * @return string
	 */
	public function getExtension(): string {
		return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
	}
	
	/**
	 * Устанавливает ограничение максимального размера файла
	 *
	 * @return void
	 */
	public function setMaxsize(): void {
		$maxsize = $this->getMaxsize();
		$iniVariable = ini_get('post_max_size') < ini_get('upload_max_filesize') ?
			'post_max_size' : 'upload_max_filesize';
		ini_set($iniVariable, $maxsize);
	}
	
	/**
	 * Получает максимальный размер файла
	 *
	 * @return int
	 */
	public function getMaxsize(): int {
		$extension = $this->getExtension();
		$postMaxsize = ini_get('post_max_size');
		$uploadMaxsize = ini_get('upload_max_filesize');
		$maxIniSizeBytes = $this->convertSizeToBytes(min($postMaxsize, $uploadMaxsize));
		$hasExtensionSizeLimit = !empty($this->settings['formats'][$extension]['maxsize']);
		
		return $hasExtensionSizeLimit ?
			$this->convertSizeToBytes($this->settings['formats'][$extension]['maxsize']) : $maxIniSizeBytes;
	}
	
	/**
	 * Конвертирует размер файла в байты
	 *
	 * @param string $iniMaxsize
	 *
	 * @return int
	 */
	private function convertSizeToBytes(string $iniMaxsize): int {
		$pow = stripos($iniMaxsize, 'K') ? 1 : (stripos($iniMaxsize, 'M') ? 2 :
			(stripos($iniMaxsize, 'G') ? 3 : 0));
		$size = (int)str_replace(['K', 'M', 'G'], '', $iniMaxsize);
		
		return $pow > 0 ? $size * pow(1024, $pow) : $size;
	}
	
	/**
	 * Проверяет запрещенные символовы в пути файла
	 *
	 * @return void
	 *
	 * @throws ForbiddenHttpException
	 */
	public function checkForbiddenCharacters(): void {
		if ($this->hasForbiddenCharacters()) {
			throw new ForbiddenHttpException("Путь к файлу содержит запрещенные символы: {$this->settings['forbidden']}");
		}
	}
	
	/**
	 * Проверяет наличие запрещенных символов в пути файла
	 *
	 * @return bool
	 */
	public function hasForbiddenCharacters(): bool {
		$hasForbiddenCharacters = false;
		
		if (isset($this->settings['forbidden'])) {
			$hasForbiddenCharacters = strpbrk($this->path, $this->settings['forbidden']) ? true : false;
		}
		
		return $hasForbiddenCharacters;
	}
}