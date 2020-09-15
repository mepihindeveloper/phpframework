<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\pattern\mvc;

/**
 * Класс, реализуйщий контроллер шаблона проектирования MVC.
 * Класс предназнаен для управления потоками данных и команд.
 *
 * @package kernel\pattern\mvc
 */
class Controller {
	
	/**
	 * @var View Представление
	 */
	private View $view;
	
	public function __construct() {
		$this->view = new View();
	}
	
	/**
	 * Возвращает представление контроллера
	 *
	 * @return View
	 */
	public function getView(): View {
		return $this->view;
	}
}