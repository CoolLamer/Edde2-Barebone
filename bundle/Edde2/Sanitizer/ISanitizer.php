<?php
	namespace Edde2\Sanitizer;

	interface ISanitizer {
		public function register($aName, ISanitizerRule $aSanitizerRule);

		/**
		 * @param string $aName
		 *
		 * @return ISanitizerRule
		 */
		public function getRule($aName);
	}
