<?php
	namespace Edde2\Sanitizer;

	interface ISanitizerRule extends IFilter {
		public function register(IFilter $aFilter);
	}
