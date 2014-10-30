<?php
	namespace Edde2\Model2\Config;

	use Edde2\Utils\Arrays;
	use Edde2\Utils\Config as UtilConfig;
	use Edde2\Utils\Strings;

	class Property extends UtilConfig {
		private static $defaults = array(
			'type' => 'string',
			'null' => false,
			'default' => null,
			'unique' => false,
		);
		private static $known = array(
			'bind',
			'nullable',
			'unique',
			'default',
			'type',
			'primary',
			'virtual',
			'comment',
		);

		public function __construct($aName) {
			$this->property('name', Strings::recamel(Strings::camelize($aName), '_'));
		}

		protected function checkConfig(array $aConfig) {
			$diff = array_diff(array_keys($aConfig), self::$known);
			if(!empty($diff)) {
				throw new UnknownDirectiveException(sprintf('Unknown property config directive: [%s] in property "%s".', implode(', ', $diff), $this->getName()));
			}
		}

		/**
		 * sestaví vlastnost ze zadané konfigurace
		 *
		 * @param array $aConfig
		 *
		 * @return $this
		 *
		 * @throws UnknownDirectiveException
		 */
		public function config(array $aConfig = null) {
			$this->checkConfig($aConfig ?: array());
			if(!empty($aConfig)) {
				$this->propertyAll($aConfig);
			}
			return $this;
		}

		public function getName() {
			return $this->get('name');
		}

		public function getCamelName() {
			return Strings::camelize($this->getName());
		}

		public function getType() {
			return $this->value('type');
		}

		public function isNullable() {
			return $this->value('nullable') === true;
		}

		public function isUnique() {
			return $this->value('unique') === true;
		}

		public function isIndex() {
			return $this->getOrDefault('index', false) === true;
		}

		public function isPrimary() {
			return $this->getOrDefault('primary', false) === true;
		}

		public function isVirtual() {
			return $this->getOrDefault('virtual', false) === true;
		}

		public function isBind() {
			return $this->has('bind');
		}

		/**
		 * @return Config|string
		 */
		public function getBind() {
			return $this->get('bind');
		}

		public function hasDefault() {
			return $this->has('default');
		}

		public function getDefault() {
			return $this->get('default');
		}

		public function setDefault(array $aDefault) {
			try {
				$this->checkConfig($aDefault);
				return parent::setDefault(Arrays::mergeTree($aDefault, self::$defaults));
			} catch(UnknownDirectiveException $e) {
				throw new UnknownDirectiveException($e->getMessage().' Check model config defaults.');
			}
		}

		public function isReference() {
			return $this->has('reference');
		}

		/**
		 * @return Property
		 */
		public function getReference() {
			return $this->get('reference');
		}

		public function hasComment() {
			return $this->has('comment');
		}

		public function getComment() {
			return $this->get('comment');
		}

		public function __toString() {
			return $this->getName();
		}
	}
