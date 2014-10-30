<?php
	namespace Edde2\Component;

	/**
	 * Specielní výjimka, jejíž zpráva se odešle do flash message.
	 */
	class FlashMessageException extends ComponentException {
		private $msg;
		private $type;

		/**
		 * @param string $aMessage
		 * @param string $aType
		 */
		public function __construct($aMessage, $aType = 'info') {
			$this->msg = $aMessage;
			$this->type = $aType;
		}

		/**
		 * @return string
		 */
		public function message() {
			return $this->msg;
		}

		/**
		 * @return string
		 */
		public function type() {
			return $this->type;
		}
	}
