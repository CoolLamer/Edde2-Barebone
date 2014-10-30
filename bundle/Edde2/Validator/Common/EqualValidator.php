<?php
	namespace Edde2\Validator\Common;

	use Edde2\Validator\Validator;

	class EqualValidator extends Validator {
		/**
		 * @var string
		 */
		private $equal;

		public function __construct($aMessage, $aEqualTo) {
			parent::__construct($aMessage);
			$this->equal = $aEqualTo;
		}

		/**
		 * vrátí true/false, pokud je hodnota validní/nevalidní; slouží pro "měkké" ověření hodnoty
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aContext = null) {
			return $aValue === $this->equal;
		}
	}
