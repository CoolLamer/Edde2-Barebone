<?php
	namespace Edde2\Query\Column;

	class ParamColumn extends Column {
		public function __construct($aColumn) {
			parent::__construct($aColumn, false);
		}

		public function table() {
			return false;
		}

		public function escape() {
			return false;
		}
	}
