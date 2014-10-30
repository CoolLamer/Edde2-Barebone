<?php
	namespace Edde2\Model2\Query;

	use Edde2\Model2\Config\PropertyException;
	use Edde2\Model2\Model;
	use Edde2\Object;
	use Edde2\Query\Column\IColumn;
	use Edde2\Query\Where\Where;

	class QueryWhere extends Object {
		/**
		 * @var Query
		 */
		private $query;
		/**
		 * @var Where
		 */
		private $where;
		/**
		 * @var QueryWhere
		 */
		private $parent;
		/**
		 * @var QueryWhere
		 */
		private $group;
		/**
		 * @var QueryWhere
		 */
		private $next;

		public function __construct(Query $aQuery, Where $aWhere, QueryWhere $aParent = null) {
			$this->query = $aQuery;
			$this->where = $aWhere;
			$this->parent = $aParent;
		}

		/**
		 * @param string|IColumn $aColumn
		 * @param string|IColumn $aParameter
		 *
		 * @throws PropertyException
		 *
		 * @return $this
		 */
		public function eq($aColumn, $aParameter) {
			$this->where->eq($this->query->column($aColumn), $aParameter);
			return $this;
		}

		public function gt($aColumn, $aParameter) {
			$this->where->gt($this->query->column($aColumn), $aParameter);
			return $this;
		}

		public function gte($aColumn, $aParameter) {
			$this->where->gte($this->query->column($aColumn), $aParameter);
			return $this;
		}

		public function lt($aColumn, $aParameter) {
			$this->where->lt($this->query->column($aColumn), $aParameter);
			return $this;
		}

		public function lte($aColumn, $aParameter) {
			$this->where->lte($this->query->column($aColumn), $aParameter);
			return $this;
		}

		public function isNull($aColumn) {
			$this->where->isNull($this->query->column($aColumn));
			return $this;
		}

		public function isNotNull($aColumn) {
			$this->where->isNotNull($this->query->column($aColumn));
			return $this;
		}

		public function group() {
			if($this->group !== null) {
				return $this->group;
			}
			return $this->group = new QueryWhere($this->query, $this->where->group(), $this);
		}

		/**
		 * @return QueryWhere
		 */
		public function orr() {
			$this->next = array(
				'OR',
				$where = new QueryWhere($this->query, $this->where->orr(), $this->parent)
			);
			return $where;
		}

		/**
		 * @return QueryWhere
		 */
		public function andd() {
			$this->next = array(
				'AND',
				$where = new QueryWhere($this->query, $this->where->andd(), $this->parent)
			);
			return $where;
		}

		public function hasWhere() {
			return $this->where->hasWhere();
		}

		/**
		 * @param array $aParams
		 *
		 * @return Model
		 */
		public function load(array $aParams = array()) {
			return $this->query->load($aParams);
		}
	}
