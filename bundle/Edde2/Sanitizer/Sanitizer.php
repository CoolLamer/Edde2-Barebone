<?php
	namespace Edde2\Sanitizer;

	use Edde2\Object;

	/**
	 * Sanitizátor - drží sady pravidel.
	 */
	class Sanitizer extends Object implements ISanitizer {
		/**
		 * @var ISanitizerRule[]
		 */
		private $rule;

		public function register($aName, ISanitizerRule $aSanitizerRule) {
			$this->rule[$aName] = $aSanitizerRule;
			return $this;
		}

		/**
		 * @param string $aName
		 *
		 * @return ISanitizerRule
		 *
		 * @throws RuleNotFoundException
		 */
		public function getRule($aName) {
			if(!isset($this->rule[$aName])) {
				throw new RuleNotFoundException("Requested rule '$aName' not found.");
			}
			return $this->rule[$aName];
		}
	}
