<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/**
 * Файл конфигурации работы с файлами.
 *
 * В данном конфигурационном файле прописываются настройки форматов и ограничений при работе с файлами.
 * Формируется массив конфигурации. Ключ массива может иметь любое название. Основные ключи обязательные
 * для заполнения: <b>formats</b>.
 *
 * В рамках формата, который определяется ключем вложенного массива в <b>formats</b>, обязательное поле для
 * заполнения - <b>mime</b>. В качестве дополнительных параметров можно указать максимальный размер файла:
 * <b>maxsize</b>. Формат установки размера совпадает с форматом конфигурирования php или apache.
 *
 * Для ограничения по имени файла добавляется ключ <b>forbidden</b> в основную секцию настроек. Поле не является
 * обязательным для заполнения. Значение поля - перечисление запрещенных символов.
 *
 * В ключ <b>active</b> прописывается название текущий используемой конфигурации (конфиграция по умолчанию).
 */
return [
	'default' => [
		'formats' => [
			'doc' => [
				'mime' => 'application/msword'
			],
			'docx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
			],
			'xls' => [
				'mime' => 'application/vnd.ms-excel'
			],
			'xlsx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
			],
			'pdf' => [
				'mime' => 'application/pdf',
				'maxsize' => '1M'
			],
			'jpg' => [
				'mime' => 'image/jpeg'
			],
			'jpeg' => [
				'mime' => 'image/jpeg'
			],
			'png' => [
				'mime' => 'image/png'
			],
			'ini' => [
				'mime' => 'text/plain'
			],
			'txt' => [
				'mime' => 'text/plain',
				'maxsize' => '100K'
			]
		],
		'forbidden' => '\/:*?"<>|+%!@',
	],
	'active' => 'default'
];