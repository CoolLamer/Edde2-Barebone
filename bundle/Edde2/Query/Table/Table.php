<?php
	namespace Edde2\Query\Table;

	use Edde2\Object;
	use Edde2\Query\Column\IColumn;
	use Edde2\Query\Query2;
	use Edde2\Query\Where\JoinWhere;
	use Edde2\Query\Where\Where;

	class Table extends Object implements \IteratorAggregate {
		/**
		 * @var Table
		 */
		private $parent;
		/**
		 * @var Query2
		 */
		private $query;
		private $table;
		private $alias;
		/**
		 * @var IColumn[]
		 */
		private $column = array();
		/**
		 * @var IColumn[]
		 */
		private $columnList = array();
		/**
		 * @var Table[]
		 */
		private $joinList = array();
		private $joinFlag = false;
		/**
		 * @var JoinWhere
		 */
		private $joinWhere;

		public function __construct(Query2 $aQuery, $aTable, Table $aParent = null) {
			$this->parent = $aParent;
			$this->query = $aQuery;
			$this->table = $aTable;
			$this->alias = $aQuery->alias($aTable);
		}

		public function format($aEscapeCallback = null) {
			$escape = $aEscapeCallback ?: function ($aString) {
				return escapeshellarg($aString);
			};
			$table[] = call_user_func($escape, $this->table);
			$table[] = ' ';
			$table[] = call_user_func($escape, $this->alias);
			if($this->hasJoinFlag()) {
				$table[] = ' ON ';
				$table[] = $this->joinWhere()->format($escape);
			}
			$joinList = array();
			foreach($this->joinList as $join) {
				$joinList[] = "\nLEFT JOIN\n\t";
				$joinList[] = $join->format($escape);
			}
			if(!empty($joinList)) {
				$table[] = implode('', $joinList);
			}
			return implode('', $table);
		}

		/**
		 * @return Table
		 */
		public function table() {
			return $this->parent;
		}

		/**
		 * přístup na kořen dotazu
		 *
		 * @return Query2
		 */
		public function query() {
			return $this->query;
		}

		public function name() {
			return $this->table;
		}

		/**
		 * @return string
		 */
		public function alias() {
			return $this->alias;
		}

		/**
		 * přidá sloupec do SELECTu; pokud je potřeba přidat aliasovaný sloupec, je nutné použít tuto funkci
		 *
		 * @param string|IColumn $aColumn
		 * @param bool $aAlias
		 *
		 * @return $this
		 */
		public function column($aColumn, $aAlias = false) {
			$column = $this->query->createColumn($aColumn, $aAlias, $this);
			$aAlias = $column->alias();
			$this->column[$aAlias ?: $column->column()] = $column;
			return $this;
		}

		/**
		 * @return $this
		 */
		public function select() {
			$all = true;
			foreach(func_get_args() as $column) {
				$this->column($column);
				$all = false;
			}
			if($all === true) {
				$this->column(array(
					$this->table,
					'*'
				));
			}
			return $this;
		}

		/**
		 * @return IColumn[]
		 */
		public function columnList() {
			if(!empty($this->columnList)) {
				return $this->columnList;
			}
			$list = $this->column;
			foreach($this->joinList as $join) {
				$list = array_merge($list, $join->columnList());
			}
			return $this->columnList = $list;
		}

		public function hasColumnList() {
			$list = $this->columnList();
			return !empty($list);
		}

		/**
		 * @return JoinWhere
		 */
		protected function joinWhere() {
			if($this->joinWhere !== null) {
				return $this->joinWhere;
			}
			return $this->joinWhere = $this->query->createJoinWhere($this);
		}

		/**
		 * @param string $aTable
		 * @param string|null $aForeign
		 * @param string $aId
		 *
		 * @return Table
		 */
		public function join($aTable, $aForeign = null, $aId = 'id') {
			$table = $this->joined($aTable);
			$where = $table->joinWhere();
			if($aForeign !== null) {
				$where->on($aForeign, $aId);
			}
			return $table;
		}

		public function joined($aTable) {
			if(isset($this->joinList[$aTable])) {
				return $this->joinList[$aTable];
			}
			$this->joinList[$aTable] = $table = $this->query->table($aTable, $this);
			return $table->setJoinFlag();
		}

		public function setJoinFlag($aIsThisNiceTableJoin = true) {
			$this->joinFlag = $aIsThisNiceTableJoin === true;
			return $this;
		}

		public function hasJoinFlag() {
			return $this->joinFlag === true;
		}

		public function hasJoinList() {
			return !empty($this->joinList);
		}

		/**
		 * @return Table[]
		 */
		public function getJoinList() {
			return $this->joinList;
		}

		/**
		 * @return Where
		 */
		public function where() {
			$where = $this->query->where();
			if($where->hasWhere()) {
				return $where->andd();
			}
			return $where;
		}

		/**
		 * @param string $aColumn
		 * @param string $aOrder
		 *
		 * @return $this
		 */
		public function order($aColumn, $aOrder = 'ASC') {
			$this->query->order(array(
				$this->table,
				$aColumn
			), $aOrder);
			return $this;
		}

		public function getIterator() {
			return new \ArrayIterator($this->column);
		}
	}
