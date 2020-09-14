<?php /*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */ /** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */
/**
 * Файл конфигурации.
 *
 * В данном конфигурационном файле находятся подключения основных конфигурационных файлов, а также некоторые общие
 * настроки приложения.
 */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
/** @noinspection UsingInclusionOnceReturnValueInspection */
return [
	'appType' => 'develop',
	'language' => 'ru',
	'friendlyUrl' => true,
	'database' => require 'database.php',
	'urls' => require 'url.php',
	'cookies' => require 'cookie.php',
	'session' => require 'session.php',
	'directories' => require 'directory.php',
	'files' => require 'file.php',
	'mail' => require 'mail.php',
	'migrations' => require 'migrations.php',
	'validators' => require 'validators.php',
	'security' => require 'security.php',
	'components' => require 'components.php',
];