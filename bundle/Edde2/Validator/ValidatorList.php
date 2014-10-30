<?php
	namespace Edde2\Validator;

	use Edde2\Object;

	class ValidatorList extends Object implements IValidatorList, \IteratorAggregate {
		/**
		 * @var IValidatorRule[]
		 */
		private $list;

		/**
		 * zaregistruje sadu validátorů pod daným jménem
		 *
		 * @param string $aName
		 * @param IValidatorRule $aValidatorRule
		 *
		 * @return self
		 */
		public function register($aName, IValidatorRule $aValidatorRule) {
			$this->list[$aName] = $aValidatorRule;
			return $this;
		}

		/**
		 * @param string $aName
		 *
		 * @throws RuleNotFoundException
		 *
		 * @return IValidatorRule
		 */
		public function getRule($aName) {
			if(!isset($this->list[$aName])) {
				throw new RuleNotFoundException(sprintf('Unknown validator rule [%s].', $aName));
			}
			return $this->list[$aName];
		}

		/**
		 * @param mixed $aValue
		 * @param string $aRule
		 * @param mixed $aContext
		 *
		 * @return self
		 */
		public function validate($aValue, $aRule, $aContext = null) {
			$this->getRule($aRule)->validate($aValue, $aContext);
			return $this;
		}

		/**
		 * @param mixed $aValue
		 * @param string $aRule
		 * @param mixed $aContext
		 *
		 * @return bool
		 */
		public function isValid($aValue, $aRule, $aContext = null) {
			return $this->getRule($aRule)->isValid($aValue, $aContext);
		}

		public function getIterator() {
			return new \ArrayIterator($this->list);
		}
	}
