<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

use kernel\Application;
use kernel\NamespaceAutoloader;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

defined('ISCLI') or define('ISCLI', php_sapi_name() === 'cli');

$root = ISCLI ? getenv('PWD') : $_SERVER['DOCUMENT_ROOT'];

defined('ROOT') or define('ROOT', "{$root}/");
defined('APPLICATION') or define('APPLICATION', "{$root}/application/");

$vendor = file_exists('vendor') ? require_once 'vendor/autoload.php' : null;

require_once 'kernel/NamespaceAutoloader.php';

$namespaceAutoloader = new NamespaceAutoloader($vendor);
Application::getInstance()->run($namespaceAutoloader);