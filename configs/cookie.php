<?php

/**
 * Файл конфигурации маршрутов.
 *
 * В данном конфигурационном файле прописываются настройка cookies.
 * Формируется массив конфигурации cookies. Ключ массива может иметь любое название. Основные ключи обязательные
 * для заполнения: expire, path, domain, secure и httponly - необходимы для корректной работы сессий.
 *
 * В ключ <b>active</b> прописывается название текущий используемой конфигурации (конфиграция по умолчанию).
 */
return [
	'default' => [
		'expire' => time() + (86400 * 30), // 86400 - 1 день
		'path' => ini_get('session.cookie_path'),
		'domain' => ini_get('session.cookie_domain'),
		'secure' => isset($_SERVER['HTTPS']),
		'httponly' => true
	],
	'active' => 'default'
];