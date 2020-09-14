<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Config;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;
use RuntimeException;

/**
 * Класс-помощник для работы с директориями.
 * Класс реализует базовый функционал по работе с директориями в области создания, удалания, копирования и другой.
 *
 * @package kernel\helpers
 */
class Dir {
	
	/**
	 * @param array $params Свойства директории:
	 * path - путь, permission - права, recursive - разрешение на создание вложенных директорий
	 *
	 * @throws ServerErrorHttpException
	 */
	public function __construct(array $params) {
		self::make($params);
	}
	
	/**
	 * Создает директорию
	 *
	 * @see https://www.php.net/manual/ru/function.mkdir.php mkdir
	 *
	 * @param array $params Свойства директории:
	 * path - путь, permission - права, recursive - разрешение на создание вложенных директорий
	 *
	 * @return void
	 *
	 * @throws ServerErrorHttpException
	 */
	private static function make(array $params): void {
		$directorySettings = Config::getInstance()->getSection('directories');
		$directoryActiveSettings = $directorySettings[$directorySettings['active']];
		
		if (!mkdir($concurrentDirectory = $params['path'], $params['permission'] ?? $directoryActiveSettings['permission'], $params['recursive'] ?? $directoryActiveSettings['recursive']) && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
	}
	
	/**
	 * Добавляет конечный "/" при его отсутствии в пути
	 *
	 * @param string $path Путь
	 *
	 * @return string
	 */
	public static function normalizeDirectoryPath(string $path): string {
		return substr($path, -1) === '/' ? $path : $path . '/';
	}
	
	/**
	 * Изменяет наименование директории
	 *
	 * @param array $params Свойства директории:
	 * path - путь, name - новое наименование, permission - права, recursive - разрешение на создание вложенных
	 *     директорий
	 *
	 * @return void
	 *
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 * @see Dir::copy()
	 *
	 */
	public static function rename(array $params): void {
		if (!is_dir($params['path'])) {
			throw new InvalidDataHttpException("Изменение названия директории не возможно,
            {$params['path']} не является дирекотрией.");
		}
		
		$pathInfo = pathinfo($params['path']);
		$directoryParams = $params;
		$directoryParams['path'] = "{$pathInfo['dirname']}/{$params['name']}";
		unset($directoryParams['name']);
		
		self::copy($params['path'], $directoryParams, true);
	}
	
	/**
	 * Копирует директорию
	 *
	 * @param string $source Директория копирования
	 * @param array $destinationParams Конечный путь копирования
	 * @param bool $needDeleteSource Необходимость в удалении исходной директории копирования
	 *
	 * @return void
	 *
	 * @throws ServerErrorHttpException
	 */
	public static function copy(string $source, array $destinationParams, bool $needDeleteSource = false): void {
		if (!file_exists($destinationParams['path'])) {
			self::make($destinationParams);
		}
		
		$directoryHandle = opendir($source);
		
		while ($file = readdir($directoryHandle)) {
			if ($file !== '.' && $file !== '..') {
				$sourceFilePath = "{$source}/{$file}";
				$destinationFilePath = "{$destinationParams['path']}/{$file}";
				
				if (is_dir($sourceFilePath)) {
					self::copy($sourceFilePath, ['path' => $destinationFilePath], $needDeleteSource);
				} else {
					copy($sourceFilePath, $destinationFilePath);
					
					if ($needDeleteSource) {
						unlink($sourceFilePath);
					}
				}
			}
		}
		
		closedir($directoryHandle);
		
		if ($needDeleteSource) {
			rmdir($source);
		}
	}
	
	/**
	 * Удаляет директорию
	 *
	 * @param string $path Путь к директории удаления
	 *
	 * @return void
	 *
	 * @throws InvalidDataHttpException
	 */
	public static function remove(string $path): void {
		if (!is_dir($path)) {
			throw new InvalidDataHttpException("Удаление директории не возможно,
            {$path} не существует или не является директорией");
		}
		
		$directoryHandle = opendir($path);
		while ($file = readdir($directoryHandle)) {
			if ($file !== '.' && $file !== '..') {
				$sourceFilePath = "{$path}/{$file}";
				
				if (is_dir($sourceFilePath)) {
					self::remove($sourceFilePath);
				} else {
					unlink($sourceFilePath);
				}
			}
		}
		
		rmdir($path);
	}
}