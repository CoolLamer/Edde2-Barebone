<?php
	namespace Edde2\Validator\Common;

	use Edde2\Utils\Strings;
	use Edde2\Validator\Validator;

	class RegExpValidator extends Validator {
		/**
		 * @var string
		 */
		private $regexp;

		public function __construct($aMessage, $aRegExp) {
			parent::__construct($aMessage);
			$this->regexp = $aRegExp;
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
			return Strings::match($aValue, $this->regexp) !== null;
		}
	}
