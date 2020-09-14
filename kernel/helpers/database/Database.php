<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers\database;

use Exception;
use kernel\Application;
use kernel\exception\DatabaseException;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Касс-помощник для работы с базой данных.
 * Класс реализует подключение и управление запросами к базе данных.
 *
 * @package kernel\helpers\database
 */
class Database {
	
	/**
	 * @var PDOStatement[] Список подготовленных запросов к базе данных
	 */
	public array $executeList = [];
	/**
	 * @var bool Статус активности транзакции
	 */
	public bool $isTransaction = false;
	/**
	 * @var array Настройки базы данных
	 */
	private array $config;
	/**
	 * @var PDO|null Соединение с базой данных
	 */
	private ?PDO $pdo;
	/**
	 * @var PDOStatement Подготовленный запрос к базе данных
	 */
	private PDOStatement $pdoStatement;
	
	/**
	 * @param string $section Профиль настроек
	 *
	 * @throws DatabaseException
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 */
	public function __construct(string $section = 'active') {
		$this->config = $section === 'active' ?
			Application::getInstance()->getConfig()->getActiveSettings('database') :
			Application::getInstance()->getConfig()->getProfileSection('database', $section);
		
		if (empty($this->config))
			throw new DatabaseException(500, 'Отсутствует конфигурация подключений к базе данных');
		
		$this->connect();
	}
	
	/**
	 * Создает подключение к базе данных
	 *
	 * @return void
	 *
	 * @throws DatabaseException
	 */
	private function connect(): void {
		$dsn = $this->config['dbms'] . ':';
		
		foreach (['host', 'dbname'] as $key) {
			$dsn .= "{$key}={$this->config[$key]};";
		}
		
		$charset = array_key_exists('charset', $this->config) ? strtoupper($this->config['charset']) : 'UTF8';
		
		try {
			$this->pdo = new PDO(
				$dsn,
				$this->config['user'],
				$this->config['password']
			);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			$this->pdo->exec("SET NAMES '{$charset}'");
		} catch (PDOException $exception) {
			throw new DatabaseException(500, $exception->getMessage(), $exception->getCode());
		}
	}
	
	/**
	 * Закрывает подключение к базе данных
	 */
	public function closeConnection() {
		$this->pdo = null;
	}
	
	/**
	 * Начинает транзакцию
	 */
	public function beginTransaction() {
		if (!$this->isTransaction) {
			$this->pdo->beginTransaction();
			$this->isTransaction = true;
		}
	}
	
	/**
	 * Выполняет транзакцию
	 *
	 * @throws DatabaseException
	 */
	public function commit() {
		try {
			if (!empty($this->executeList)) {
				foreach ($this->executeList as $executeQuery) {
					$executeQuery->execute();
				}
			}
			
			$this->pdo->commit();
		} catch (Exception $exception) {
			$this->pdo->rollBack();
			throw new DatabaseException(500, $exception->getMessage(), $exception->getCode());
		} finally {
			$this->isTransaction = false;
			$this->executeList = [];
		}
	}
	
	/**
	 * Возвращает массив, содержащий все строки результирующего набора
	 *
	 * @see https://www.php.net/manual/ru/pdostatement.fetchall.php PDOStatement::fetchAll
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты
	 * @param int $fetchStyle Определяет содержимое возвращаемого массива
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	public function queryAll(string $query, array $attributes = [], $fetchStyle = PDO::FETCH_ASSOC): array {
		$this->execute($query, $attributes);
		
		return $this->pdoStatement->fetchAll($fetchStyle);
	}
	
	/**
	 * Выполняет запрос
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты
	 *
	 * @return bool
	 * @throws DatabaseException
	 */
	public function execute(string $query, array $attributes = []): bool {
		$this->beforeQuery($query, $attributes);
		
		return $this->pdoStatement->execute();
	}
	
	/**
	 * Обработка запроса перед выполненениме
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты запроса
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	private function beforeQuery(string $query, array $attributes = []): void {
		try {
			$this->pdoStatement = $this->pdo->prepare($query);
			
			if (!empty($attributes)) {
				$bindedAttributes = $this->bindAttributes($attributes);
				
				foreach ($bindedAttributes as $bindedAttribute) {
					$attributesPart = explode("\x7F", $bindedAttribute);
					$this->pdoStatement->bindParam($attributesPart[0], $attributesPart[1]);
				}
			}
			
			if ($this->isTransaction) {
				$this->executeList[] = $this->pdoStatement;
			}
		} catch (PDOException $exception) {
			throw new DatabaseException(500, $exception->getMessage(), $exception->getCode());
		}
	}
	
	/**
	 * Назначает атрибуты
	 *
	 * @param array $attributes Атрибуты
	 *
	 * @return array
	 */
	private function bindAttributes(array $attributes) {
		$bindedAttributes = [];
		
		foreach ($attributes as $key => $value) {
			$bindedAttributes[] = ':' . $key . "\x7F" . $value;
		}
		
		return $bindedAttributes;
	}
	
	/**
	 * Возвращает строку результирующего набора
	 *
	 * @see https://www.php.net/manual/ru/pdostatement.fetch.php PDOStatement::fetch
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты
	 * @param int $fetchStyle Определяет содержимое возвращаемого массива
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function queryRow(string $query, array $attributes = [], $fetchStyle = PDO::FETCH_ASSOC) {
		$this->execute($query, $attributes);
		
		return $this->pdoStatement->fetch($fetchStyle);
	}
	
	/**
	 * Возвращает колонку результирующего набора
	 *
	 * @see https://www.php.net/manual/ru/pdostatement.fetchcolumn.php PDOStatement::fetchColumn
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	public function queryColumn(string $query, array $attributes = []): array {
		$this->execute($query, $attributes);
		$queryCells = $this->pdoStatement->fetchAll(PDO::FETCH_NUM);
		$cells = [];
		
		foreach ($queryCells as $queryCell) {
			$cells[] = $queryCell[0];
		}
		
		return $cells;
	}
	
	/**
	 * Возвращает единственную запись результирующего набора
	 *
	 * @param string $query Запрос
	 * @param array $attributes Атрибуты
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function queryOne(string $query, array $attributes = []) {
		$this->execute($query, $attributes);
		
		return $this->pdoStatement->fetchColumn();
	}
	
	/**
	 * Возвращает ID последней вставленной строки или значение последовательности
	 *
	 * @see https://www.php.net/manual/ru/pdo.lastinsertid.php PDO::lastInsertId
	 *
	 * @return string
	 */
	public function getLastInsertId(): string {
		return $this->pdo->lastInsertId();
	}
}