<?php
	namespace Edde2\Query\Where;

	use Edde2\Query\Query2;
	use Edde2\Query\Table\Table;

	class JoinWhere extends Where {
		/**
		 * @var Table
		 */
		private $table;

		public function __construct(Query2 $aQuery, Where $aParent = null, Table $aTable) {
			parent::__construct($aQuery, $aParent);
			$this->table = $aTable;
		}

		/**
		 * @param string $aForeign
		 * @param string $aId
		 *
		 * @return Table
		 */
		public function on($aForeign, $aId = 'id') {
			if(!$this->table->table()->hasJoinFlag()) {
				$jump = $aId;
				$aId = $aForeign;
				$aForeign = $jump;
			}
			$this->eq($this->query()->createColumn($aId, false, $this->table), $this->query()->createColumn($aForeign, false, $this->table->table()));
			return $this->table;
		}
	}
