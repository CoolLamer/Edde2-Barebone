<?php
	namespace Edde2\Query\Where;

	use Edde2\Object;
	use Edde2\Query\Column\IColumn;
	use Edde2\Query\GroupWhereExpressionException;
	use Edde2\Query\Query2;
	use Edde2\Query\WhereExpressionException;

	class Where extends Object {
		/**
		 * @var Query2
		 */
		private $query;
		/**
		 * @var Where
		 */
		private $parent;
		/**
		 * @var Where
		 */
		private $group;
		/**
		 * @var array(IColumn, string, IColumn)
		 */
		private $where;
		/**
		 * @var array(string, Where)
		 */
		private $next;

		public function __construct(Query2 $aQuery, Where $aParent = null) {
			$this->query = $aQuery;
			$this->parent = $aParent;
		}

		public function format($aEscapeCallback = null) {
			$escape = $aEscapeCallback ?: function ($aString) {
				return escapeshellarg($aString);
			};
			$format = array();
			/** @var $where Where[] - IDE cheat, neni to tak uplne pravda */
			$where = array(
				null,
				$this
			);
			while($where) {
				$list = array();
				if($where[1]->hasWhere()) {
					/** @var $expression IColumn[] */
					$expression = $where[1]->where;
					$column = $expression[0]->format($escape, true);
					$value = null;
					if(isset($expression[2])) {
						$value = $expression[2]->format($escape, true);
					}
					if($where[0] !== null) {
						$list[] = " $where[0] ";
					}
					switch($expression[1]) {
						case '=':
							$list[] = "$column = $value";
							break;
						case 'LIKE':
							$list[] = "$column LIKE $value";
							break;
						case 'IS NULL':
							$list[] = "$column IS NULL";
							break;
						case 'IS NOT NULL':
							$list[] = "$column IS NOT NULL";
							break;
						case '<':
							$list[] = "$column < $value";
							break;
						case '<=':
							$list[] = "$column <= $value";
							break;
						case '>':
							$list[] = "$column > $value";
							break;
						case '>=':
							$list[] = "$column >= $value";
							break;
					}
				}
				if($where[1]->isGroup()) {
					$list[] = " $where[0] ";
					$list[] = '(';
					$list[] = $where[1]->group->format($escape);
					$list[] = ')';
				}
				$format[] = implode('', $list);
				$where = $where[1]->next;
			}
			return implode('', $format);
		}

		protected function expression(IColumn $aColumn, $aExpression, IColumn $aParameter = null) {
			if($this->isGroup()) {
				throw new GroupWhereExpressionException('Cannot set where expression when grouping another wheres.');
			}
			$this->where = array(
				$aColumn,
				$aExpression,
				$aParameter
			);
			return $this;
		}

		/**
		 * ekvivalentní (rovná se)
		 *
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function eq($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), '=', $this->query->createColumn($aParameter));
		}

		/**
		 * vetší než
		 *
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function gt($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), '>', $this->query->createColumn($aParameter));
		}

		/**
		 * menší než
		 *
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function lt($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), '<', $this->query->createColumn($aParameter));
		}

		/**
		 * vetší nebo rovno než
		 *
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function gte($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), '>=', $this->query->createColumn($aParameter));
		}

		/**
		 * menší nebo rovno než
		 *
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function lte($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), '<=', $this->query->createColumn($aParameter));
		}

		/**
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @return Where
		 */
		public function like($aColumn, $aParameter) {
			return $this->expression($this->query->createColumn($aColumn), 'LIKE', $aParameter);
		}

		public function isNull($aColumn) {
			return $this->expression($this->query->createColumn($aColumn), 'IS NULL');
		}

		public function isNotNull($aColumn) {
			return $this->expression($this->query->createColumn($aColumn), 'IS NOT NULL');
		}

		/**
		 * @return Where
		 */
		public function parent() {
			return $this->parent;
		}

		/**
		 * @return Query2
		 */
		public function query() {
			return $this->query;
		}

		/**
		 * vytvoří skupinu podmínek pod touto (tzn. uzavře do závorek)
		 *
		 * @throws WhereExpressionException
		 *
		 * @return Where
		 */
		public function group() {
			if($this->hasWhere()) {
				throw new WhereExpressionException('Cannot group where with expression. Create AND/OR relation and use group() method.');
			}
			if($this->group !== null) {
				return $this->group;
			}
			return $this->group = $this->query->createWhere($this);
		}

		/**
		 * @return Where
		 */
		public function orr() {
			$this->next = array(
				'OR',
				$where = $this->query->createWhere($this->parent)
			);
			return $where;
		}

		/**
		 * @return Where
		 */
		public function andd() {
			$this->next = array(
				'AND',
				$where = $this->query->createWhere($this->parent)
			);
			return $where;
		}

		public function hasNext() {
			return $this->next !== null;
		}

		/**
		 * @return array
		 */
		public function next() {
			return $this->next;
		}

		public function isGroup() {
			return !empty($this->group);
		}

		public function getGroup() {
			return $this->group;
		}

		public function hasWhere() {
			return $this->where !== null;
		}

		public function getWhere() {
			return $this->where;
		}
	}
