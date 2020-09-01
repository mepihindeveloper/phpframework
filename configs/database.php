<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации баз данных.
 *
 * В данном конфигурационном файле прописываются настройки подключения к базе данных.
 * Формируется массив конфигурации для подключения. Ключ массива может иметь любое название. Основные ключи
 * обязательные для заполнения: <b>dbms</b>, <b>host</b>, <b>dbname</b>, <b>user</b>, <b>password</b>.
 *
 * В ключ <b>active</b> прописывается название текущий используемой конфигурации (конфиграция по умолчанию).
 */
return [
	'pgsql' => [
		'dbms' => 'pgsql',
		'host' => 'localhost',
		'dbname' => 'phpframework',
		'user' => 'www-data',
		'password' => 'pass'
	],
	'mysql' => [
		'dbms' => 'mysql',
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => ''
	],
	'active' => 'pgsql'
];