<?php
	Phar::mapPhar('${phar}');
	require_once('phar://${phar}/Nette/loader.php');
	require_once('phar://${phar}/Edde2/loader.php');
	return new \Edde2\Bootstrap\Configurator($root);
	__HALT_COMPILER();
