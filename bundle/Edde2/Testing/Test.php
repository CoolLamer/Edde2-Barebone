<?php
	namespace Edde2\Testing;

	use Edde2\DI\Container;
	use Tester\TestCase;

	abstract class Test extends TestCase {
		/**
		 * @var Container
		 */
		private $context;

		public function injectContext(Container $aContext) {
			$this->context = $aContext;
		}

		public static function execute(Container $aContext, $aTest) {
			$test = $aContext->createInstance($aTest);
			$aContext->callInjects($test);
			$test->run();
		}
	}
