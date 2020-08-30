<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

use kernel\helpers\Url;

?>
	<p><?= $name ?></p>
<?php

Url::to(['controller' => 'basic', 'action' => 'print'], ['a' => '1', 'b' => '2']);
?>