<?php
	namespace Edde2\Validator;

	interface IValidatorList {
		/**
		 * zaregistruje sadu validátorů pod daným jménem
		 *
		 * @param string $aName
		 * @param IValidatorRule $aValidatorRule
		 *
		 * @return self
		 */
		public function register($aName, IValidatorRule $aValidatorRule);

		/**
		 * @param string $aName
		 *
		 * @throws RuleNotFoundException
		 *
		 * @return IValidatorRule
		 */
		public function getRule($aName);

		/**
		 * @param mixed $aValue
		 * @param string $aRule
		 * @param mixed $aContext
		 *
		 * @return self
		 */
		public function validate($aValue, $aRule, $aContext = null);

		/**
		 * @param mixed $aValue
		 * @param string $aRule
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aRule, $aContext = null);
	}
