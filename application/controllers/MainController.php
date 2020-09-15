<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace application\controllers;

use kernel\pattern\mvc\Controller;

/**
 * Класс MainController.
 * Базовый класс домашней страницы.
 *
 * @package application\controllers
 */
class MainController extends Controller {
	
	public function actionIndex(): void {
		$name = 'Привет мир!';
		$this->getView()->render('index.php', compact('name'));
	}
}