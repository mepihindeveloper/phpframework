<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers\file;

use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;

/**
 * Касс-помощник для работы с INI файлами.
 * Класс расширяет возможности класса File. Класс реализует шаблон проектировани "Строитель".
 * В представленном классе идет реализация функционала для работы с INI файлами.
 * В качестве базового функционала предсталено создание, добавлени и чтение секций, добавлени и удалени ключей и другой
 * функционал.
 *
 * @package kernel\helpers\file
 */
class Ini extends File {
	
	/**
	 * @var array Структура ini файла
	 */
	private array $structure = [];
	/**
	 * @var int Тип сканирования
	 */
	private int $scannerMode = INI_SCANNER_TYPED;
	
	/**
	 * Ini constructor.
	 *
	 * @see https://www.php.net/manual/ru/function.parse-ini-file.php parse_ini_file scanner_mode
	 *
	 * @param string $path Путь до файла
	 * @param int $scannerMode Тип сканирования из
	 *
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 */
	public function __construct(string $path, int $scannerMode = INI_SCANNER_TYPED) {
		parent::__construct($path);
		
		if ($this->file->getExtension() !== 'ini') {
			throw new InvalidDataHttpException('Ошибка создания ini файла. Файл должен иметь расширение ini');
		}
		
		$this->file->create();
		
		$this->scannerMode = $scannerMode;
		$this->setInitStructure();
	}
	
	/**
	 * Парсит структуру ini фалйа
	 *
	 * @return void
	 */
	private function setInitStructure(): void {
		$this->structure = parse_ini_file($this->path, true, $this->scannerMode);
	}
	
	/**
	 * Возвращает структуру ini файла
	 *
	 * @return array
	 */
	public function getStructure(): array {
		return $this->structure;
	}
	
	/**
	 * Получает значение ключа в секции
	 *
	 * @param string $section Название секции
	 * @param string $key Ключ
	 *
	 * @return array
	 */
	public function getValue(string $section, string $key): array {
		return $this->getSection($section)[$key];
	}
	
	/**
	 * Получает ключи и значения секции
	 *
	 * @param string $section Название секции
	 *
	 * @return array
	 */
	public function getSection(string $section): array {
		return $this->structure[$section];
	}
	
	/**
	 * Добавляет секцию
	 *
	 * @param string $section Название секции
	 *
	 * @return Ini
	 *
	 * @throws InvalidDataHttpException
	 */
	public function addSection(string $section): Ini {
		if (array_key_exists($section, $this->structure)) {
			throw new InvalidDataHttpException("Секция {$section} уже существует");
		}
		
		$this->structure[$section] = [];
		
		return $this;
	}
	
	/**
	 * Назначает зачения ключам секции
	 *
	 * @param string $section Название секции
	 * @param array $values Массив значений [ключ => значение]
	 *
	 * @return Ini
	 */
	public function setValues(string $section, array $values): Ini {
		foreach ($values as $key => $value) {
			$this->structure[$section][$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Добавляет значения в секцию
	 *
	 * @param string $section Название секции
	 * @param array $values Массив значений [ключ => значение]
	 *
	 * @return Ini
	 */
	public function addValues(string $section, array $values): Ini {
		$this->structure[$section] = array_merge($values, $this->structure[$section]);
		
		return $this;
	}
	
	/**
	 * Записывает и сохраняет измененя
	 *
	 * @return void
	 */
	public function write(): void {
		$iniContent = null;
		
		foreach ($this->structure as $section => $data) {
			$iniContent .= "[{$section}]\n";
			
			foreach ($data as $key => $value) {
				$iniContent .= "{$key}={$value}\n";
			}
			
			$iniContent .= "\n";
		}
		
		$this->file->rewrite($iniContent);
		$this->setInitStructure();
	}
	
	/**
	 * Удаляет ключи секции
	 *
	 * @param string $section Название секции
	 * @param array $keys Ключи для удаления
	 *
	 * @return Ini
	 */
	public function removeKeys(string $section, array $keys): Ini {
		foreach ($keys as $key) {
			unset($this->structure[$section][$key]);
		}
		
		return $this;
	}
	
	/**
	 * Удаляет секцию
	 *
	 * @param string $section Название секции
	 *
	 * @return Ini
	 */
	public function removeSection(string $section): Ini {
		unset($this->structure[$section]);
		
		return $this;
	}
}