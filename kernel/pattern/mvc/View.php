<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\pattern\mvc;

use http\Exception\RuntimeException;
use kernel\exception\NotFoundHttpException;

/**
 * Класс, реализуйщий представление шаблона проектирования MVC.
 * Класс предназнаен для управления представлениями.
 *
 * @package kernel\pattern\mvc
 */
class View {
	
	/**
	 * @var string Шаблон страницы по умолчанию
	 */
	private string $defaultLayout = 'layout.php';
	
	/**
	 * @param string $view Представление
	 * @param array $data Данные, внутри представления
	 * @param string $layout Шаблон представления
	 *
	 * @throws NotFoundHttpException
	 */
	public function render(string $view, array $data = [], string $layout = '') {
		$viewFile = APPLICATION . "views/{$view}";
		$viewLayout = APPLICATION . 'views/layouts/' . (!empty($layout) ? $layout : $this->defaultLayout);
		
		if (!is_file($viewFile) || !is_file($viewLayout)) {
			throw new NotFoundHttpException("Представление {$view} или шаблон {$layout}
            по пути {$viewFile} или {$viewLayout} не найдены");
		}
		
		$countVariables = extract($data, EXTR_SKIP);
		
		if ($countVariables !== count($data)) {
			throw new RuntimeException('Ошибка извлечения: попытка изменения области видимости');
		}
		
		ob_start();
		require_once $viewFile;
		$content = ob_get_contents();
		ob_get_clean();
		
		require_once $viewLayout;
	}
	
	/**
	 * Устанавливает шаблон страницы по умолчанию
	 *
	 * @param string $layoutPath Шаблон страницы
	 */
	public function setDefaultLayout(string $layoutPath): void {
		$this->defaultLayout = $layoutPath;
	}
}