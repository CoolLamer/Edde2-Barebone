<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;

	class Timer extends Object {
		private $begin;
		private $elapsed;
		/**
		 * umožní upravit hodnotu časovače; hodnota se přičítá
		 *
		 * @var float
		 */
		private $fix = 0;
		/**
		 * @var Timer[]
		 */
		private $child = array();
		private $epsilon;
		private $note;

		/**
		 * @param float $aEpsilon
		 * @param bool $aAutostart
		 */
		public function __construct($aEpsilon = null, $aAutostart = true) {
			$this->epsilon = $aEpsilon;
			if($aAutostart === true) {
				$this->start();
			}
		}

		public function start() {
			$this->elapsed = null;
			$this->begin = microtime(true);
			return $this;
		}

		public function stop($aNote = null) {
			if($this->elapsed === null) {
				$this->elapsed = microtime(true) - $this->begin;
				$this->note = $aNote;
			}
			return $this;
		}

		/**
		 * @param Timer $aChild
		 *
		 * @return $this|Timer[]
		 */
		public function child(Timer $aChild = null) {
			if($aChild === null) {
				return $this->child;
			}
			$this->child[] = $aChild;
			return $this;
		}

		public function fix($aFix) {
			$this->fix = $aFix;
			return $this;
		}

		/**
		 * zastaví timer a vrátí uběhnutý čas
		 *
		 * @param bool $aRaw pokud je true, vrátí čistý čas behu bez dalších úprav
		 *
		 * @return float
		 */
		public function getElapsed($aRaw = false) {
			$this->stop();
			if($aRaw === true) {
				return $this->elapsed;
			}
			$elapsed = $this->elapsed + $this->fix;
//			foreach($this->child as $timer) {
//				$elapsed -= $timer->getElapsed();
//			}
			return $elapsed;
		}

		/**
		 * @return string
		 */
		public function getNote() {
			return $this->note;
		}

		public function needAttention() {
			if($this->epsilon === null) {
				return false;
			}
			return $this->elapsed >= $this->epsilon;
		}
	}
