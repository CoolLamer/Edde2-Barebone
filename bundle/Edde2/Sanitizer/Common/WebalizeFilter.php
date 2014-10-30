<?php
	namespace Edde2\Sanitizer\Common;

	use Edde2\Sanitizer\Filter;
	use Edde2\Utils\Strings;

	class WebalizeFilter extends Filter {
		public function input($aInput) {
			return Strings::webalize($aInput);
		}
	}
