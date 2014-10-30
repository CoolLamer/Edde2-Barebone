<?php
	namespace Edde2\Templating;

	class CreateException extends TemplatingException {
		/**
		 * @var string
		 */
		private $name;

		/**
		 * @param string $aMessage
		 * @param string $aName
		 */
		public function __construct($aMessage, $aName) {
			parent::__construct($aMessage);
			$this->name = $aName;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
	}
