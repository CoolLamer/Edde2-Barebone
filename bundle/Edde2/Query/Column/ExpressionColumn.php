<?php
	namespace Edde2\Query\Column;

	use Edde2\Query\Table\Table;

	class ExpressionColumn extends Column {
		public function __construct($aExpression, Table $aTable = null) {
			parent::__construct($aExpression, false, $aTable);
		}

		public function escape() {
			return false;
		}
	}
