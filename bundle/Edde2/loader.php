<?php
	/**
	 * některé třídy je potřeba načíst manuálně před registrací RobotLoaderu
	 */
	$classList = array(
		'Utils/Object.php',
		'Bootstrap/Configurator.php',
		'DI/Container.php',
		'Reflection/MetaInfo.php',
		'Reflection/EddeLoader.php',
	);
	if(!defined('T_TRAIT')) {
		define('T_TRAIT', -1);
	}
	foreach($classList as $source) {
		require(__DIR__.'/'.$source);
	}
