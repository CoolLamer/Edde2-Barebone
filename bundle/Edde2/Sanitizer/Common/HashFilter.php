<?php
	namespace Edde2\Sanitizer\Common;

	use Edde2\Sanitizer\Filter;
	use Edde2\Utils\Strings;

	class HashFilter extends Filter {
		public function input($aInput) {
			return Strings::hash($aInput, $aInput, 'sha1');
		}
	}
