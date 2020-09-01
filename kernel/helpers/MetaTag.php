<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

/**
 * Класс-помощник для работы с мета тегами.
 * Класс позволяет реализовать упрощшенную генерацию мета тегов. Базовые методы реализуют мета теги:
 * <b>author</b>, <b>description</b>, <b>keywords</b>. Также, класс позволяет реализовать собственные мета теги.
 *
 * Мета теги могут добавляться в контроллере и регистрироваться в шаблонах.
 *
 * @package kernel\helpers
 */
class MetaTag {
	
	/**
	 * @var array Мета теги
	 */
	private static array $metaTags = [];
	
	/**
	 * Регистрирует и формирует HTML код мега тегов
	 *
	 * @return string
	 */
	public static function register(): string {
		$metaCode = null;
		
		foreach (self::$metaTags as $metaTag) {
			$metaCode .= "<meta ";
			
			array_walk($metaTag, function($value, $key) use (&$metaCode) {
				$metaCode .= "{$key}='{$value}'";
			});
			
			$metaCode .= ">" . PHP_EOL;
		}
		
		return $metaCode;
	}
	
	/**
	 * Добавляет мета тег автора
	 *
	 * @param string $content Данные мета тега
	 *
	 * @return $this
	 */
	public function addAuthor(string $content): MetaTag {
		self::$metaTags[] = ['name' => 'author', 'content' => $content];
		
		return $this;
	}
	
	/**
	 * Добавляет мета тег ключевых слов
	 *
	 * @param string $content Данные мета тега
	 *
	 * @return $this
	 */
	public function addKeywords(string $content): MetaTag {
		self::$metaTags[] = ['name' => 'keywords', 'content' => $content];
		
		return $this;
	}
	
	/**
	 * Добавляет мета тег описания
	 *
	 * @param string $content Данные мета тега
	 *
	 * @return $this
	 */
	public function addDescription(string $content): MetaTag {
		self::$metaTags[] = ['name' => 'description', 'content' => $content];
		
		return $this;
	}
	
	/**
	 * Добавляет пользовательский мета тег
	 *
	 * @param array $metaTagAttributes Данные мета тега
	 *
	 * @return $this
	 */
	public function addCustom(array $metaTagAttributes): MetaTag {
		self::$metaTags[] = $metaTagAttributes;
		
		return $this;
	}
	
	/**
	 * Возвращает мета теги
	 *
	 * @return array
	 */
	public function getMetaTags(): array {
		return self::$metaTags;
	}
}