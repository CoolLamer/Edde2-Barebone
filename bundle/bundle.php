<?php
	namespace bundle;

	$eddeBundlePhar = 'edde2.bundle.phar';
	$eddeBundlePharPath = '../lib/edde2.bundle.phar';
	try {
		$time = microtime(true);
		@unlink($eddeBundlePharPath);
		$phar = new \Phar($eddeBundlePharPath);
		$phar->interceptFileFuncs();
		$phar->buildFromDirectory(__DIR__);
		$phar->setStub(str_replace('${phar}', $eddeBundlePhar, file_get_contents(__DIR__.'/stub.php')));
//		$phar->compressFiles(\Phar::GZ);
		echo sprintf("%s, %.2fs\n", $eddeBundlePhar, microtime(true) - $time);
	} catch(\Exception $e) {
		die($e->getMessage());
	}
