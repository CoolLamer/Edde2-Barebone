<?php
	namespace Edde2\Query;

	use Edde2\Object;
	use Edde2\Query\Column\AsteriskColumn;
	use Edde2\Query\Column\CommonColumn;
	use Edde2\Query\Column\IColumn;
	use Edde2\Query\Column\ParamColumn;
	use Edde2\Query\Column\PlaceholderColumn;
	use Edde2\Query\Table\JoinTable;
	use Edde2\Query\Table\Table;
	use Edde2\Query\Where\JoinWhere;
	use Edde2\Query\Where\Where;
	use Edde2\Utils\Strings;

	/**
	 * Třída je jako abstraktní označena úmyslně - přestože umožňuje do jisté míry bezpečně stavět databázové dotazy, není plnohodnotně
	 * funkční dokud se neokonkretizuje její využití - ať již pod databází se schopností přímo pokládat dotazy (+ zajistění escapovací funkce)
	 * nebo např. ve vrstvě modelů k implementaci kontroly dotazu na dostupné sloupce/tabulky.
	 */
	abstract class Query2 extends Object {
		/**
		 * @var Table[]
		 */
		private $table = array();
		private $alias = array();
		/**
		 * @var Where
		 */
		private $where;
		/**
		 * @var IColumn[]
		 */
		private $order;
		private $limit = 0;
		private $offset;

		/**
		 * tovární metoda na vytvoření sloupce pro různé využití (SELECT, WHERE, ....)
		 *
		 * @param string|IColumn $aColumn
		 * @param bool $aAlias
		 * @param Table $aTable
		 *
		 * @return IColumn
		 */
		public function createColumn($aColumn, $aAlias = false, Table $aTable = null) {
			$column = $aColumn;
			if(is_array($column)) {
				$aTable = $this->table($column[0]);
				$column = $column[1];
			}
			if(is_string($column)) {
				if($column[0] === ':') {
					$column = new ParamColumn($column);
				} else {
					$column = explode(':', $column);
					if(isset($column[1])) {
						$aAlias = $column[1];
					}
					$column = $column[0];
				}
			}
			if(!($column instanceof IColumn)) {
				switch($column) {
					case '*':
						$column = new AsteriskColumn($aTable);
						break;
					case '?':
						$column = new PlaceholderColumn();
						break;
					default:
						$column = new CommonColumn($column, $aAlias, $aTable);
				}
			}
			return $column;
		}

		/**
		 * tovární metoda pro vytvoření obyčejné tabulky (tzn. do FROM klauzule)
		 *
		 * @param string $aTable
		 * @param Table $aParent
		 *
		 * @return Table
		 */
		public function createTable($aTable, Table $aParent = null) {
			return new Table($this, $aTable, $aParent);
		}

		/**
		 * @param string $aTable
		 * @param Table $aParent
		 *
		 * @return JoinTable
		 */
		public function createJoinTable($aTable, Table $aParent) {
			return new JoinTable($this, $aTable, $aParent);
		}

		/**
		 * @param Where $aParent
		 *
		 * @return Where
		 */
		public function createWhere(Where $aParent = null) {
			return new Where($this, $aParent);
		}

		/**
		 * @param Table $aTable
		 *
		 * @return JoinWhere
		 */
		public function createJoinWhere(Table $aTable) {
			return new JoinWhere($this, null, $aTable);
		}

		public function alias($aString) {
			$alias = Strings::upperLetters($aString, true);
			if(isset($this->alias[$alias]) && $this->alias[$alias] !== $aString) {
				$count = 2;
				while(isset($this->alias[$alias.$count])) {
					$count++;
				}
				$alias = $alias.$count;
			}
			$this->alias[$alias] = $aString;
			return $alias;
		}

		/**
		 * @param string $aTable
		 * @param Table $aJoin
		 *
		 * @return Table
		 */
		public function table($aTable, Table $aJoin = null) {
			if(isset($this->table[$aTable])) {
				return $this->table[$aTable];
			}
			if($aJoin !== null) {
				return $this->table[$aTable] = $this->createJoinTable($aTable, $aJoin);
			}
			return $this->table[$aTable] = $this->createTable($aTable);
		}

		public function where() {
			if($this->where === null) {
				$this->where = $this->createWhere();
			}
			return $this->where->group();
		}

		public function whereAnd() {
			$where = $this->where();
			if($where->hasWhere()) {
				$where = $where->andd();
			}
			return $where;
		}

		public function whereOr() {
			$where = $this->where();
			if($where->hasWhere()) {
				$where = $where->orr();
			}
			return $where;
		}

		/**
		 * @param string|IColumn $aColumn
		 * @param string $aOrder
		 *
		 * @return $this
		 */
		public function order($aColumn, $aOrder = 'ASC') {
			$this->order[] = array(
				$this->createColumn($aColumn),
				/** tento zapis umyslne vlozi vychozi razeni ASC, pokud byl zadan blabol; velikost pismen se nekontroluje, protoze nemusi prijit string */
				$aOrder === 'DESC' ? 'DESC' : 'ASC'
			);
			return $this;
		}

		public function limit($aLimit, $aOffset = null) {
			$this->limit = max(0, $aLimit);
			if(max(0, $aOffset) > 0) {
				$this->offset = (int)$aOffset;
			}
			return $this;
		}

		public function format($aEscapeCallback = null) {
			$sql[] = 'SELECT';
			$tableList = array();
			$escape = $aEscapeCallback ?: function ($aString) {
				return escapeshellarg($aString);
			};
			$select = array();
			foreach($this->table as $table) {
				foreach($table->columnList() as $column) {
					$select[] = $column->format($escape);
				}
				if(!$table->hasJoinFlag()) {
					$tableList[] = $table->format($escape);
				}
			}
			if(empty($select)) {
				$select[] = '*';
			}
			$select = array_unique($select);
			$sql[] = implode(',', $select);
			$sql[] = 'FROM';
			$sql[] = implode(',', $tableList);
			if($this->where !== null) {
				$sql[] = 'WHERE';
				$sql[] = $this->where->group()->format($escape);
			}
			if(!empty($this->order)) {
				$sql[] = 'ORDER BY';
				$orderList = array();
				/** @var $order IColumn[] IDE cheat */
				foreach($this->order as $order) {
					$formattedOrder = $order[0]->format($escape, true);
					$orderList[$formattedOrder] = $formattedOrder.' '.$order[1];
				}
				$sql[] = implode(',', $orderList);
			}
			if($this->limit !== null && $this->limit > 0) {
				$sql[] = 'LIMIT';
				$sql[] = $this->limit;
				if($this->offset !== null && $this->offset >= 0) {
					$sql[] = 'OFFSET';
					$sql[] = $this->offset;
				}
			}
			return implode(' ', $sql);
		}
	}
