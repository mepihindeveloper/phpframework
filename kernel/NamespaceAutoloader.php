<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel;

use Composer\Autoload\ClassLoader;

/**
 * Класс для работы с пространством имен.
 * Класс предназначен для автоматической загрузки используемых классов.
 *
 * @package kernel
 */
class NamespaceAutoloader {
	
	/**
	 *
	 * @var array Карта для соответствия namespace пути в файловой системе
	 */
	private array $map = [];
	/**
	 * @var ClassLoader|null
	 */
	private ?ClassLoader $vendor;
	
	public function __construct(ClassLoader $vendor = null) {
		$this->vendor = $vendor;
		$this->add('kernel', ROOT . 'kernel/');
		$this->register();
	}
	
	/**
	 * Добавляет namespace в очередь на подключение
	 *
	 * @param string $namespace Namespace
	 * @param string $root Корневая папка namespace
	 *
	 * @return void
	 */
	public function add(string $namespace, string $root): void {
		$rootNormalized = substr($root, -1) == '/' ? $root : $root . '/';
		if (!is_dir($rootNormalized))
			return;
		
		$this->map[$namespace][] = $rootNormalized;
		$subDirectories = $this->getSubDirectories("{$rootNormalized}*");
		
		if (!is_array($subDirectories))
			return;
		
		foreach ($subDirectories as $subDirectory) {
			$this->map[$namespace][] = $subDirectory;
		}
	}
	
	/**
	 * Получает список вложенных директорий
	 *
	 * @param string $dir Путь к директории для поиска вложенных структур
	 *
	 * @return array
	 */
	private function getSubDirectories(string $dir): array {
		$directories = array_filter(glob("{$dir}*"), 'is_dir');
		$subDirectories = $directories;
		
		foreach ($directories as $directory) {
			$subDirectories = array_merge($subDirectories, $this->getSubDirectories($directory . '/*'));
		}
		
		return $subDirectories;
	}
	
	/**
	 * Регистрирует namespace
	 *
	 * @return void
	 */
	public function register(): void {
		spl_autoload_register([$this, 'autoload']);
	}
	
	/**
	 * Получение карты классов
	 *
	 * @return array
	 */
	public function getMap(): array {
		return $this->map;
	}
	
	public function getVendor(): ClassLoader {
		return $this->vendor;
	}
	
	/**
	 * Подключает необходимые файлы классов по namespace
	 *
	 * @param string $class Класс
	 *
	 * @return void
	 */
	private function autoload(string $class): void {
		$pathParts = explode('\\', $class);
		
		if (!is_array($pathParts))
			return;
		
		$namespace = array_shift($pathParts);
		$namespaceDirectories = $this->map[$namespace];
		
		if (empty($namespaceDirectories))
			return;
		
		foreach ($namespaceDirectories as $namespaceDirectory) {
			$filePath = "{$namespaceDirectory}/" . end($pathParts) . '.php';
			
			if (file_exists($filePath)) {
				require_once $filePath;
				break;
			}
		}
	}
}