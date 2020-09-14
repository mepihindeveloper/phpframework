<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации.
 *
 * В данном конфигурационном файле находятся подключения основных конфигурационных файлов, а также некоторые общие
 * настроки приложения.
 */
return [
	'appType' => 'develop',
	'language' => 'ru',
	'friendlyUrl' => true,
	'database' => require_once 'database.php',
	'urls' => require_once 'url.php',
	'cookies' => require_once 'cookie.php',
	'session' => require_once 'session.php',
	'directories' => require_once 'directory.php',
	'files' => require_once 'file.php',
	'mail' => require_once 'mail.php',
	'migrations' => require_once 'migrations.php',
	'validators' => require_once 'validators.php',
	'security' => require_once 'security.php',
	'components' => require_once 'components.php',
];