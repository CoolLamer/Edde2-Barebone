<?php
	namespace Edde2\Utils\Process;

	use Edde2\EddeException;

	class ProcessException extends EddeException {
		/**
		 * @var string
		 */
		private $runcall;

		/**
		 * @param string $aMessage
		 * @param string $aRuncall
		 */
		public function __construct($aMessage, $aRuncall) {
			parent::__construct($aMessage);
			$this->runcall = $aRuncall;
		}

		/**
		 * @return string
		 */
		public function getRuncall() {
			return $this->runcall;
		}
	}
