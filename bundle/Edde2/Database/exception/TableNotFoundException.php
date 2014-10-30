<?php
	namespace Edde2\Database;

	class TableNotFoundException extends DatabaseException {
		private $table;

		public function __construct($aMessage, $aTable, \Exception $aException) {
			parent::__construct($aMessage,0, $aException);
			$this->table = $aTable;
		}

		public function getTable() {
			return $this->table;
		}
	}
