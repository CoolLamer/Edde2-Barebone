<?php
	namespace Edde2\DI;

	use Edde2\Object;

	/**
	 * Třída užitečná v případě implementace proxy služeb (lazy loading); je možné její pomocí reagovat na absenci dané služby.
	 */
	abstract class Accessor extends Object implements IAccessor {
		/**
		 * @var Container
		 */
		private $context;
		private $instance;
		private $service;
		private $fallback;

		public function __construct(Container $aContainer, $aService, $aFallback = null) {
			$this->context = $aContainer;
			$this->service = $aService;
			$this->fallback = $aFallback;
		}

		public function get() {
			if($this->instance === null) {
				$this->instance = $this->context->getService($this->service);
			}
			return $this->instance;
		}
	}
