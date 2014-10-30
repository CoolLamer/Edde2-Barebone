<?php
	namespace Edde2\Model2\Config;

	use Edde2\Model2\ModelException;

	class PropertyException extends ModelException {
		/**
		 * @var string
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
