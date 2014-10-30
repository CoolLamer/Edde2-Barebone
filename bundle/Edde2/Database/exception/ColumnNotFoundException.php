<?php
	namespace Edde2\Database;

	class ColumnNotFoundException extends DatabaseException {
		private $column;

		public function __construct($aMessage, $aColumn, \Exception $aException) {
			parent::__construct($aMessage, 0, $aException);
			$this->column = $aColumn;
		}

		public function getColumn() {
			return $this->column;
		}
	}
