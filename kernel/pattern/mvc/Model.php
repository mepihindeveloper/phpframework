<?php
declare(strict_types = 1);

namespace kernel\pattern\mvc;

use kernel\helpers\database\Database;

/**
 * Класс, реализуйщий модель шаблона проектирования MVC.
 * Класс предназнаен для управления данными (представлением таблицы) базы данных.
 *
 * @package kernel\pattern\mvc
 */
class Model {
	
	/**
	 * @var Database Экземпляр класса базы данных
	 */
	protected Database $database;
	
	public function __construct() {
		$this->database = new Database();
	}
	
	public function __destruct() {
		$this->database->closeConnection();
	}
}