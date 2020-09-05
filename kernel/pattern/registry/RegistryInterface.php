<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

namespace kernel\pattern\registry;

/**
 * Интерфейс классов, реализующих шаблон проектирования "Реестр"
 *
 * @package kernel\pattern\registry
 */
interface RegistryInterface {
	
	/**
	 * Инициализация реестра
	 *
	 * @return mixed
	 */
	public function init();
	
	/**
	 * Получение компонента.
	 * В случае отсутствия пользовательского компонента.
	 * Если получения компонента ядра не удалось, то выдает ошибку
	 *
	 * @param string $name Название компонента
	 *
	 * @return mixed
	 */
	public function get(string $name);
}