<?php
declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Application;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\ServerErrorHttpException;

/**
 * Класс-помощник для работы с ссылками.
 * Класс реализует формирование ссылки для перехода в рамках маршрутизации и получени параметров адресной строки.
 *
 * @package kernel\helpers
 */
class Url {
	
	/**
	 * Формирует ссылку для перехода
	 *
	 * @param array $routerAttributes Атрибуты для маршрутизации: module, controller, action
	 * @param array $options Дополнительные параметры маршрутизации
	 *
	 * @return string
	 *
	 * @throws InvalidDataHttpException
	 * @throws ServerErrorHttpException
	 */
	public static function to(array $routerAttributes, array $options = []): string {
		$application = Application::getInstance();
		$isFriendlyUrl = $application->getConfig()->getSection('friendlyUrl');
		$link = $isFriendlyUrl ? '/' : 'index.php?';
		
		if ($application->getRouter()->hasUrlModule()) {
			$link .= $isFriendlyUrl ? $routerAttributes['module'] . '/' : 'module=' . $routerAttributes['module'] . '&';
		}
		
		$unifiedAttributes = $application->getRouter()->unifyAttributes($routerAttributes);
		$normalizedAttributes = [];
		
		foreach (['controller', 'action'] as $requiredAttribute) {
			if (!in_array($requiredAttribute, array_keys($routerAttributes))) {
				$normalizedAttributes[$requiredAttribute] = $application->getRouter()->getDefaultProperty($requiredAttribute);
			} else {
				$normalizedAttributes[$requiredAttribute] = ($routerAttributes[$requiredAttribute]);
			}
		}
		
		$normalizedAttributes = array_merge($normalizedAttributes, array_diff_key($unifiedAttributes, $normalizedAttributes));
		
		foreach ($normalizedAttributes as $attribute => $value) {
			$link .= $isFriendlyUrl ? "{$value}/" : "{$attribute}={$value}";
		}
		
		$url = rtrim($link, ($isFriendlyUrl ? '/' : '&'));
		
		if (!empty($options)) {
			$url .= ($isFriendlyUrl ? '?' : '&') . self::getOptions($application->getRouter()->getMethodParams($options));
		}
		
		return $url;
	}
	
	/**
	 * Получает строки параметров ссылки
	 *
	 * @param array $options Параметры ссылки
	 *
	 * @return string
	 */
	private static function getOptions(array $options): string {
		$urlOptions = null;
		
		foreach ($options as $name => $value) {
			$urlOptions .= "{$name}={$value}&";
		}
		
		return rtrim($urlOptions, '&');
	}
}