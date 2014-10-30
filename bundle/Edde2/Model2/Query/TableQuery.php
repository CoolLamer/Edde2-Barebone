<?php
	namespace Edde2\Model2\Query;

	use Edde2\Model2\Config\Config;
	use Edde2\Object;
	use Edde2\Query\Table\Table;

	class TableQuery extends Object {
		/**
		 * @var Query
		 */
		private $query;
		/**
		 * @var Config
		 */
		private $modelConfig;
		/**
		 * @var Table
		 */
		private $table;
		/**
		 * @var BindQuery[]
		 */
		private $bind;

		public function __construct(Query $aQuery, Config $aModelConfig, Table $aTable) {
			$this->query = $aQuery;
			$this->modelConfig = $aModelConfig;
			$this->table = $aTable;
		}

		/**
		 * @return Query
		 */
		public function query() {
			return $this->query;
		}

		/**
		 * @param string $aBind
		 *
		 * @return BindQuery
		 */
		public function bind($aBind) {
			if(isset($this->bind[$aBind])) {
				return $this->bind[$aBind];
			}
			$config = $this->query->config($aBind);
			$table = $this->table->join($config->getSourceName(), $this->modelConfig->getBind($aBind)->getReference()->getName());
			return $this->bind[$aBind] = new BindQuery($this->query, $config, $table);
		}

		/**
		 * @param string $aBind
		 *
		 * @return BindQuery
		 */
		public function bindReverse($aBind) {
			if(isset($this->bind[$aBind])) {
				return $this->bind[$aBind];
			}
			$config = $this->query->config($aBind);
			$table = $this->table->join($config->getSourceName(), 'id', $config->getBind($this->modelConfig->getName())->getReference()->getName());
			return $this->bind[$aBind] = new BindQuery($this->query, $config, $table);
		}

		public function select() {
			$this->query->target($this->modelConfig->getName());
			$this->table->select('*');
			return $this;
		}

		/**
		 * @return Table
		 */
		public function table() {
			return $this->table;
		}

		/**
		 * @return QueryWhere
		 */
		public function where() {
			return $this->query->where();
		}
	}
