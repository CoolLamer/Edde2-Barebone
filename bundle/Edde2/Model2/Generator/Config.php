<?php
	namespace Edde2\Model2\Generator;

	use Edde2\Utils\ObjectEx;

	class Config extends ObjectEx {
		public function getPath() {
			return $this->get('path');
		}
	}
