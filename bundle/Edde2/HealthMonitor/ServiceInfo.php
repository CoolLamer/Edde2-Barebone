<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;

	class ServiceInfo extends Object {
		private $name;
		private $timer;
		private $class;
		private $count = 0;
		/**
		 * @var ServiceInfo[]
		 */
		private $depend = array();

		public function __construct($aName, Timer $aTimer) {
			$this->name = $aName;
			$this->timer = $aTimer;
		}

		/**
		 * @return mixed
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * @return Timer
		 */
		public function getTimer() {
			return $this->timer;
		}

		/**
		 * @param string $aClass
		 *
		 * @return $this
		 */
		public function setClass($aClass) {
			$this->class = $aClass;
			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getClass() {
			return $this->class;
		}

		public function getCount() {
			return $this->count;
		}

		public function increment() {
			$this->count++;
			return $this;
		}

		/**
		 * @param ServiceInfo $aServiceInfo
		 *
		 * @return $this|ServiceInfo[]
		 */
		public function depend(ServiceInfo $aServiceInfo = null) {
			if($aServiceInfo === null) {
				return $this->depend;
			}
			$this->depend[$aServiceInfo->getName()] = $aServiceInfo;
			return $this;
		}

		public function getElapsed() {
			return 0;
		}
	}
