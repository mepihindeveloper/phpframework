<?php /*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

/** @var string $content Результат рендера представления */

use application\assets\IconsAsset; ?>
<!doctype html>

<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
	
	<title>Базовый шаблон</title>
	
	<?= IconsAsset::register(IconsAsset::TYPE_CSS) ?>
</head>

<body>
<?= $content ?>
</body>
</html>