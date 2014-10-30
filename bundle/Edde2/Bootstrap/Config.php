<?php
	namespace Edde2\Bootstrap;

	use Edde2\Utils\TreeObjectEx;
	use Nette\DI\Container;

	abstract class Config extends TreeObjectEx {
		public function __construct(Container $aContainer) {
			parent::__construct($aContainer->getParameters());
		}

		/**
		 * je daná konfigurační sekce dostupná? - tzn. není prázdná
		 *
		 * @return bool
		 */
		public function isAvailable() {
			return !$this->none();
		}
	}
