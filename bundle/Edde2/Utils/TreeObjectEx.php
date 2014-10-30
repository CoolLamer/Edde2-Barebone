<?php
	namespace Edde2\Utils;

	/**
	 * Stromová verze obecného objektu pouze přebalí veškerá vstupní pole do dalších stromových objektů.
	 */
	class TreeObjectEx extends ObjectEx {
		public function __construct(array $aProperties = null) {
			/**
			 * úmyslně chybí parent::__construct
			 */
			$this->build($aProperties);
		}

		/**
		 * sestaví stromový objekt, pokud nebyl nastaven z konstruktoru
		 *
		 * @param array $aProperties
		 *
		 * @return $this
		 */
		public function build(array $aProperties = null) {
			$properties = $aProperties ?: array();
			foreach($properties as $property => &$value) {
				if(is_array($value)) {
					$value = new self($value);
				}
			}
			$this->propertyAll($properties);
			return $this;
		}

		public function toArray() {
			$array = $this->current();
			foreach($array as &$v) {
				if($v instanceof TreeObjectEx) {
					$v = $v->toArray();
				}
			}
			return $array;
		}
	}
