<?php
	namespace Edde2\Validator;

	interface IValidatorRule extends IValidator {
		/**
		 * zaregistruje validátor do této sady pravidel (přidá se jako poslední)
		 *
		 * @param IValidator $aValidator
		 *
		 * @return self
		 */
		public function register(IValidator $aValidator);
	}
