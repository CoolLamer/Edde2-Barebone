<?php
	namespace Edde2\Utils;

	class UnknownPropertyException extends PropertyException {
		/**
		 * @var array
		 */
		private $properties;

		public function __construct($aMessage, $aProperty, array $aProperties) {
			parent::__construct($aMessage, $aProperty);
			$this->properties = $aProperties;
		}

		/**
		 * @return array
		 */
		public function getProperties() {
			return $this->properties;
		}
	}
