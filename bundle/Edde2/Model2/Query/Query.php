<?php
	namespace Edde2\Model2\Query;

	use Edde2\Database\Query as DatabaseQuery;
	use Edde2\Model2\Config\CannotFindBindException;
	use Edde2\Model2\Config\Config;
	use Edde2\Model2\Config\UnknownUniquePropertyException;
	use Edde2\Model2\EmptyResultException;
	use Edde2\Model2\Holder\Holder;
	use Edde2\Model2\Model;
	use Edde2\Object;
	use Edde2\Query\Table\Table;
	use Nette\Utils\Validators;

	/**
	 * Třída sloužící pro využití SQL dotazů tak trochu jinak.
	 */
	class Query extends Object implements \IteratorAggregate {
		/**
		 * @var Holder
		 */
		private $holder;
		/**
		 * @var DatabaseQuery
		 */
		private $query;
		/**
		 * @var Config
		 */
		private $modelConfig;
		/**
		 * jméno modelu, který se bude vytahovat do kolekce (používá se v případě MN vazeb)
		 *
		 * @var string
		 */
		private $collectionOf;
		/**
		 * klíč, dle kterého se bude indexovat kolekce (ve výchoyím stavu ID, může být cokoli)
		 *
		 * @var string
		 */
		private $collectionKey;
		/**
		 * @var Model
		 */
		private $model;
		/**
		 * @var Table
		 */
		private $table;
		/**
		 * @var BindQuery[]
		 */
		private $bind;
		/**
		 * @var QueryWhere
		 */
		private $where;

		/**
		 * @param DatabaseQuery $aQuery
		 * @param Config|Model $aModel
		 * @param Holder $aHolder
		 */
		public function __construct(DatabaseQuery $aQuery, $aModel, Holder $aHolder) {
			$this->query = $aQuery;
			$this->modelConfig = $aModel instanceof Model ? $aModel->config() : $aModel;
			$this->model = $aModel instanceof Model ? $aModel : null;
			$this->holder = $aHolder;
		}

		public function select() {
			$this->ensureTable()->table->select();
			return $this;
		}

		public function target($aModel) {
			$this->collectionOf = $aModel;
			return $this;
		}

		/**
		 * @param string $aModel
		 *
		 * @return Config
		 */
		public function config($aModel) {
			return $this->holder->config($aModel);
		}

		protected function ensureTable($aTable = null) {
			if($this->table === null) {
				$this->table = $this->query->table($aTable ?: $this->modelConfig->getSourceName());
			}
			return $this;
		}

		/**
		 * @param string $aModel
		 *
		 * @throws CannotFindBindException
		 * @throws UnknownUniquePropertyException
		 *
		 * @return BindQuery
		 */
		public function bind($aModel) {
			if(isset($this->bind[$aModel])) {
				return $this->bind[$aModel];
			}
			$this->ensureTable();
			$modelConfig = $this->holder->config($aModel);
			return $this->bind[$aModel] = new BindQuery($this, $modelConfig, $this->table->join($modelConfig->getSourceName(), $modelConfig->getBind($this->modelConfig->getName())->getReference()->getName()));
		}

		public function mn($aWannaThisGuy, $aThroughThisGuy) {
			$this->target($aWannaThisGuy);
			$throughConfig = $this->config($aThroughThisGuy);
			$this->ensureTable($throughConfig->getSourceName());
			if($this->model === null) {
				throw new MissingModelException('Query has been created without model. Cannot resolve M:N reference.');
			}
			$config = $this->config($aWannaThisGuy);
			$bind = $this->table->join($config->getSourceName(), 'id', $throughConfig->getBind($config->getName())->getReference()->getName());
			$bind->select();
			$bind->where()->eq(array(
				$throughConfig->getSourceName(),
				$throughConfig->getBind($this->modelConfig->getName())->getReference()->getName()
			), ':id');
			$this->query->argz(array(':id' => $this->model->getId()));
			return $this;
		}

		public function bindReverse($aModel) {
			if(isset($this->bind[$aModel])) {
				return $this->bind[$aModel];
			}
			$modelConfig = $this->holder->config($aModel);
			$this->ensureTable();
			return $this->bind[$aModel] = new BindQuery($this, $modelConfig, $this->table->join($modelConfig->getSourceName(), 'id', $this->modelConfig->getBind($modelConfig->getName())->getReference()->getName()));
		}

		public function hasWhere() {
			return $this->where->hasWhere();
		}

		public function where() {
			if($this->where === null) {
				$this->where = new QueryWhere($this, $this->query->where());
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

		public function order($aProperty, $aOrder = 'ASC') {
			$this->query->order($this->column($aProperty), $aOrder);
			return $this;
		}

		public function limit($aLimit, $aOffset = null) {
			$this->query->limit($aLimit, $aOffset);
			return $this;
		}

		public function column($aColumn) {
			$column = $aColumn;
			if(is_array($column)) {
				$modelConfig = $this->holder->config($column[0]);
				$column[0] = $modelConfig->getSourceName();
				$propertyName = explode(':', $column[1]);
				$property = $modelConfig->getProperty(reset($propertyName));
				$column[1] = $property->getName();
				if($property->isBind() && $property->isVirtual()) {
					$column[1] = $property->getReference()->getName();
				}
				if(isset($propertyName[1])) {
					$column[1] .= ':'.$propertyName[1];
				}
			}
			return $column;
		}

		protected function resolve($aResolve = null) {
			if($aResolve === null) {
				return array();
			}
			$name = $this->modelConfig->getName();
			if(Validators::isNumericInt($aResolve)) {
				$this->where()->eq(array(
					$name,
					'id'
				), ':id');
				return array(':id' => $aResolve);
			}
			if(is_string($aResolve)) {
				$this->where()->eq(array(
					$name,
					$this->modelConfig->getUnique()->getName()
				), ':unique');
				return array(':unique' => $aResolve);
			}
			if(is_array($aResolve)) {
				foreach($aResolve as $column => $value) {
					$this->whereAnd()->eq(array(
						$this->table->name(),
						$column
					), ":$column");
					$this->argz(array(
						":$column" => $value
					));
				}
			}
			return array();
		}

		public function argz(array $aArgz) {
			$this->query->argz($aArgz);
			return $this;
		}

		public function getArgz() {
			return $this->query->getArgz();
		}

		/**
		 * @param string|int|null $aResolve
		 *
		 * @throws EmptyResultException
		 *
		 * @return Model
		 */
		public function load($aResolve = null) {
			$this->ensureTable();
			$resultSet = $this->query->query($this->resolve($aResolve));
			if(($model = $this->model) === null || $this->collectionOf !== null) {
				$model = $this->holder->model($this->collectionOf ?: $this->modelConfig->getName());
			}
			if(($row = $resultSet->fetch()) === false) {
				throw new EmptyResultException('Cannot load data with given query ['.$this->format().'].');
			}
			$model->putAll((array)$row);
			return $model;
		}

		public function format() {
			return $this->query->format();
		}

		public function collection() {
			$collection = array();
			$key = 'id';
			if($this->collectionKey !== null) {
				$property = $this->holder->config($this->collectionOf ?: $this->modelConfig->getName())->getProperty($this->collectionKey);
				$key = $property->getName();
				if($property->isBind() && $property->isVirtual()) {
					$key = $property->getReference()->getName();
				}
			}
			foreach($this->getIterator() as $model) {
				$collection[$model->get($key)] = $model;
			}
			return $collection;
		}

		public function setCollectionKey($aProperty) {
			$this->collectionKey = $aProperty;
			return $this;
		}

		public function getIterator() {
			$this->ensureTable();
			return new ModelIterator($this->holder, $this->collectionOf ?: $this->modelConfig->getName(), $this->query->query(), $this->collectionKey);
		}
	}
