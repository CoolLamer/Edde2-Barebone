<?php
	namespace Edde2\Utils;

	class PropertyException extends ObjectExException {
		/**
		 * @var string požadovaná properta
		 */
		private $property;

		/**
		 * @param string $aMessage
		 * @param string $aProperty
		 */
		public function __construct($aMessage, $aProperty = null) {
			parent::__construct($aMessage);
			$this->property = $aProperty;
		}

		/**
		 * @return string
		 */
		public function getProperty() {
			return $this->property;
		}
	}
