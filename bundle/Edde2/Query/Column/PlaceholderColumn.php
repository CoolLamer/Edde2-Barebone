<?php
	namespace Edde2\Query\Column;

	class PlaceholderColumn extends ExpressionColumn {
		public function __construct() {
			parent::__construct('?');
		}
	}
