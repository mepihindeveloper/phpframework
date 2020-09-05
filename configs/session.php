<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации сессий.
 *
 * В данном конфигурационном файле прописываются профили работы с сессиями (все временные параметры в секундах).
 * Формируется массив конфигурации. Ключ массива может иметь любое название. Основные ключи обязательные
 * для заполнения: <b>lifeTime</b>, <b>inactionLifeTime</b>, <b>idLifeTime</b>, <b>use_cookies</b>,
 * <b>use_only_cookies</b>.
 *
 * <b>lifeTime</b> - Время жизни сессии в секундах
 * <b>inactionLifeTime</b> - Время жизни сессии при бездействии в секундах
 * <b>idLifeTime</b> - Время жизни идентификатора сессии в секундах
 * <b>use_cookies</b> - Определяет, будет ли модуль использовать cookies для хранения идентификатора сессии на стороне
 * клиента
 * <b>use_only_cookies</b> - Определяет, будет ли модуль использовать только cookies для хранения идентификатора сессии
 * на стороне клиента
 *
 * Значение 0 - не имеет значения, то есть параметр не будет использоваться или имеет бессрочный период действия.
 *
 * В ключ <b>active</b> прописывается название текущий используемой конфигурации (конфиграция по умолчанию).
 */
return [
	'default' => [
		'lifeTime' => '5',
		'inactionLifeTime' => '0',
		'idLifeTime' => '0',
	],
	'withCookies' => [
		'lifeTime' => ini_get('session.gc_maxlifetime'),
		'inactionLifeTime' => '0',
		'idLifeTime' => '0',
		'cookies' => [
			'use_cookies' => '1',
			'use_only_cookies' => '1'
		],
	],
	'short' => [
		'lifeTime' => '20',
		'inactionLifeTime' => '15',
		'idLifeTime' => '5'
	],
	'secure' => [
		'inactionLifeTime' => '300',
		'idLifeTime' => '900'
	],
	'active' => 'default'
];