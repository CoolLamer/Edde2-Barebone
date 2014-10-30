<?php
	namespace Edde2\Query\Column;

	use Edde2\Query\Table\Table;

	class AsteriskColumn extends ExpressionColumn {
		public function __construct(Table $aTable = null) {
			parent::__construct('*', $aTable);
		}
	}
