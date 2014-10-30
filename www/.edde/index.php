<?php
	/**
	 * tento index míří do root správy aplikace
	 */
	namespace index;

	use Edde2\Bootstrap\Configurator;

	/** @var $config Configurator */
	$config = require(__DIR__.'/../../app/bootstrap.php');
	$context = $config->build();
	$context->getService('application')->run();
