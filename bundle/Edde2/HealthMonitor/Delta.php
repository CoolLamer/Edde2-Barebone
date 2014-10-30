<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;

	/**
	 * Informace o běhu - počátek, delta (od posledního měření) a od počátku
	 */
	class Delta extends Object {
		private $begin;
		private $delta;
		private $lifetime;
		private $epsilon;

		public function __construct($aBegin, $aDelta, $aLifetime, $aEpsilon) {
			$this->begin = $aBegin;
			$this->delta = $aDelta;
			$this->lifetime = $aLifetime;
			$this->epsilon = $aEpsilon;
		}

		/**
		 * @return int
		 */
		public function getBegin() {
			return $this->begin;
		}

		/**
		 * @return int
		 */
		public function getDelta() {
			return $this->delta;
		}

		/**
		 * @return int
		 */
		public function getLifetime() {
			return $this->lifetime;
		}

		/**
		 * řekne, zda tato delta potřebuje pozornost, čili trvala déle, než je předaný epsilon
		 *
		 * @return bool
		 */
		public function needAttention() {
			return $this->delta >= $this->epsilon;
		}
	}
