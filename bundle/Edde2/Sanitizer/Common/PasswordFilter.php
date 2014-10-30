<?php
	namespace Edde2\Sanitizer\Common;

	use Edde2\Sanitizer\Filter;
	use Nette\Security\Passwords;

	/**
	 * Zahashuje vstup jako heslo
	 */
	class PasswordFilter extends Filter {
		public function input($aInput) {
			return Passwords::hash($aInput, array('cost' => 10));
		}
	}
