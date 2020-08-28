<?php

use kernel\helpers\Url;

?>
	<p><?= $name ?></p>
<?php

Url::to(['controller' => 'basic', 'action' => 'print'], ['a' => '1', 'b' => '2']);
?>