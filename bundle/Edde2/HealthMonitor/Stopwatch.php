<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;

	/**
	 * Stopky měří čas mezi pojmenovanými milníky.
	 */
	class Stopwatch extends Object implements \IteratorAggregate {
		/**
		 * od kdy se měří
		 *
		 * @var int
		 */
		private $begin;
		/**
		 * jednotlivé rundy měření
		 *
		 * @var int[]
		 */
		private $round = array();

		public function __construct($aAutostart = true) {
			if($aAutostart === true) {
				$this->start();
			}
		}

		public function start() {
			if($this->begin !== null) {
				return $this;
			}
			$this->begin = microtime(true);
			return $this;
		}

		/**
		 * @param string $aName
		 * @param int $aEpsilon
		 *
		 * @return Delta
		 */
		public function round($aName, $aEpsilon = null) {
			$stamp = microtime(true);
			return $this->round[$aName] = new Delta($this->begin, $stamp - (empty($this->round) ? $this->begin : ($this->begin + end($this->round)->getLifetime())), $stamp - $this->begin, $aEpsilon);
		}

		public function getIterator() {
			return new \ArrayIterator($this->round);
		}
	}
