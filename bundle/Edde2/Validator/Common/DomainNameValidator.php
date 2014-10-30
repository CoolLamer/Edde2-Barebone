<?php
	namespace Edde2\Validator\Common;

	use Edde2\Utils\Strings;
	use Edde2\Validator\Validator;

	class DomainNameValidator extends Validator {
		/**
		 * vrátí true/false, pokud je hodnota validní/nevalidní; slouží pro "měkké" ověření hodnoty
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aContext = null) {
			if(!is_string($aValue)) {
				return false;
			}
			return Strings::match($aValue, '~^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$~') !== null;
		}
	}
