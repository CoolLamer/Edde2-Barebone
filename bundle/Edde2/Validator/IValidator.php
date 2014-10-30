<?php
	namespace Edde2\Validator;

	interface IValidator {
		/**
		 * ověří danou hodnotu a v případě, že není validní, vyhodí výjimku
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext kontext, ve kterém probíhá validace, např. valdiace hodnoty objektu
		 *
		 * @throws ValueException
		 *
		 * @return self
		 */
		public function validate($aValue, $aContext = null);

		/**
		 * vrátí true/false, pokud je hodnota validní/nevalidní; slouží pro "měkké" ověření hodnoty
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aContext = null);
	}
