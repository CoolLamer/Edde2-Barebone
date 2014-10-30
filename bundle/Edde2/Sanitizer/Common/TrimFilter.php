<?php
	namespace Edde2\Sanitizer\Common;

	use Edde2\Sanitizer\Filter;
	use Edde2\Utils\Strings;

	class TrimFilter extends Filter {
		public function input($aInput) {
			return Strings::trim($aInput);
		}
	}
