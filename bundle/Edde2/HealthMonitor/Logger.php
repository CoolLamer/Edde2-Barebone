<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;

	abstract class Logger extends Object implements ILogger {
		/**
		 * @var string
		 */
		private $name;

		public function __construct($aName) {
			$this->name = $aName;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
	}
