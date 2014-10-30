<?php
	namespace Edde2\Validator\Common;

	use Edde2\Validator\Validator;

	class BoolValidator extends Validator {
		/**
		 * vrátí true/false, pokud je hodnota validní/nevalidní; slouží pro "měkké" ověření hodnoty
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aContext = null) {
			return is_bool($aValue);
		}
	}
