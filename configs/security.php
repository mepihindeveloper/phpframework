<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации безопасности.
 * Для включения csrf модуля безопасности необходимо указать enable = true. <b>lifeTime</b> - время жизни токена.
 * При значении 0 - токен не будет обновлять до момента завершения сессии. <b>useCookies</b> отвечает за использование
 * csrf токена в $_COOKIES, а не HTML атрибуте. Это позволит не передавать его при запросах.
 */
return [
	'csrf' => [
		'enable' => false,
		'lifeTime' => '10',
		'useCookies' => false
	]
];