<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации отправки почтовых сообщений.
 *
 * В данном конфигурационном файле прописываются настройки подключения к почтовому серверу.
 * Формируется массив конфигурации. Ключ массива может иметь любое название. Основные ключи обязательные
 * для заполнения: <b>smtpUsername</b>, <b>smtpPassword</b>, <b>smtpHost</b>, <b>smtpPort</b>,
 * <b>smtpCharset</b>, <b>smtpUseSSL</b>, <b>smtpTimeout</b>, <b>smtpFrom</b>.
 *
 * В рамках отправителя, обязательное поле для заполнения - <b>name</b>, <b>email</b>.
 *
 * В ключ <b>active</b> прописывается название текущий используемой конфигурации (конфиграция по умолчанию).
 */
return [
	'default' => [
		'smtpUsername' => '',
		'smtpPassword' => '',
		'smtpHost' => '',
		'smtpPort' => 465,
		'smtpCharset' => 'utf-8',
		'smtpUseSSL' => true,
		'smtpTimeout' => 30,
		'smtpFrom' => [
			'name' => '',
			'email' => ''
		]
	],
	'active' => 'default'
];