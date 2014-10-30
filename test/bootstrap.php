<?php
	namespace bootstrap;

	require_once(__DIR__.'/../lib/Tester/bootstrap.php');
	$configuraotr = require_once(__DIR__.'/../app/bootstrap.php');
	return $configuraotr->disableDebugMode()->build();
