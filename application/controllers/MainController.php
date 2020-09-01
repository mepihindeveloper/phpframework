<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace controllers;

use kernel\pattern\mvc\Controller;

class MainController extends Controller {
	
	public function actionIndex() {
		$name = 'Привет мир!';
		$this->getView()->render('index.php', compact('name'));
	}
}