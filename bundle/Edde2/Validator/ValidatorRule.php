<?php
	namespace Edde2\Validator;

	use Edde2\Object;

	class ValidatorRule extends Object implements IValidatorRule {
		/**
		 * @var IValidator[]
		 */
		private $validator = array();

		/**
		 * zaregistruje validátor do této sady pravidel (přidá se jako poslední)
		 *
		 * @param IValidator $aValidator
		 *
		 * @return self
		 */
		public function register(IValidator $aValidator) {
			$this->validator[] = $aValidator;
			return $this;
		}

		/**
		 * ověří danou hodnotu a v případě, že není validní, vyhodí výjimku
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @throws ValueException
		 *
		 * @return self
		 */
		public function validate($aValue, $aContext = null) {
			foreach($this->validator as $validator) {
				$validator->validate($aValue, $aContext);
			}
			return $this;
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
			foreach($this->validator as $validator) {
				if($validator->isValid($aValue, $aContext) === false) {
					return false;
				}
			}
			return true;
		}
	}
