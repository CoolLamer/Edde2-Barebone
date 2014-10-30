<?php
	namespace Edde2\Model2\Query;

	use Edde2\Model2\Holder\Holder;
	use Edde2\Model2\Model;
	use Edde2\Object;
	use Nette\Database\ResultSet;

	class ModelIterator extends Object implements \Iterator {
		/**
		 * @var Holder
		 */
		private $holder;
		/**
		 * @var string
		 */
		private $name;
		/**
		 * @var ResultSet
		 */
		private $resultSet;
		/**
		 * @var string
		 */
		private $key;
		/**
		 * @var Model
		 */
		private $current;

		public function __construct(Holder $aHolder, $aName, $aResultSet, $aKey = null) {
			$this->holder = $aHolder;
			$this->name = $aName;
			$this->resultSet = $aResultSet;
			$this->key = $aKey;
		}

		public function current() {
			return $this->current;
		}

		public function next() {
			$this->resultSet->next();
		}

		public function key() {
			if($this->key !== null) {
				$property = $this->current->getModelProperty($this->key);
				return $this->current->get($property->isBind() && $property->isVirtual() ? $property->getReference()->getName() : $property->getName());
			}
			return $this->resultSet->key();
		}

		public function valid() {
			if(!$this->resultSet->valid()) {
				return false;
			}
			$row = $this->resultSet->current();
			$this->current = $this->modelService->model($this->name);
			$this->current->putAll((array)$row);
			if(count($row) === 1) {
				if(($id = max(0, reset($row)) > 0)) {
					$this->current->load($id);
				}
			}
			return true;
		}

		public function rewind() {
			$this->resultSet->rewind();
		}
	}
