<?php
	namespace Edde2\Model2\Config;

	use Edde2\Utils\Arrays;
	use Edde2\Utils\NoPropertiesException;
	use Edde2\Utils\ObjectEx;
	use Edde2\Utils\Strings;
	use Nette\Neon\Neon;

	class Config extends ObjectEx {
		private static $known = array(
			'name',
			'extend',
			'default',
			'property',
			'unique',
			'bind',
			'index',
			'comment',
			'virtual',
		);
		/**
		 * @var Property[]
		 */
		private $propertyList = array();
		/**
		 * pole primárních klíčů - pouze formalita
		 *
		 * @var Property[]
		 */
		private $primartList = array();
		/**
		 * @var Property[]
		 */
		private $requiredList = array();
		/**
		 * pole unikátních vlastností
		 *
		 * @var Property[]
		 */
		private $uniqueList = array();
		/**
		 * pole vlastností, které tvoří skupiny unikátních vlastností
		 *
		 * @var Property[][]
		 */
		private $uniqueGroup = array();
		/**
		 * skupiny vlastností, které společně tvoří indexy
		 *
		 * @var Property[][]
		 */
		private $indexGroup = array();
		/**
		 * vlastnosti, které ukazují na jiné modely
		 *
		 * @var Property[]
		 */
		private $bindList = array();
		/**
		 * @var Property[]
		 */
		private $modelBindList = array();
		/**
		 * pole vygenerovaných M:N modelů
		 *
		 * @var Config[]
		 */
		private $bindModel = array();
		/**
		 * vypočítané MN vazby, pokud je model má (array(Config, Config))
		 *
		 * @var Config[][]
		 */
		private $mn = array();
		/**
		 * připojené modely (tzn. koukají na tento)
		 *
		 * @var Config[]
		 */
		private $bound = array();

		protected function configUnique(array $aConfig) {
			foreach($aConfig as $group) {
				$this->uniqueGroup[] = Arrays::forceArray($group);
			}
			return $this;
		}

		protected function configIndex(array $aConfig) {
			foreach($aConfig as $group) {
				$this->indexGroup[] = Arrays::forceArray($group);
			}
			return $this;
		}

		protected function configBind(array $aBind) {
			foreach($aBind as $modelName) {
				$modelConfig = new Config();
				$config = sprintf("
name: %s%s
comment: 'Spojovací model (pro M:N vazbu)'
property:
	%s:
		bind: %s
	%s:
		bind: %s
						", $this->getName(), $modelName, Strings::recamel($this->getName(), '_'), $this->getName(), Strings::recamel($modelName, '_'), $modelName);
				$this->bindModel[] = $modelConfig->config(Neon::decode($config));
			}
		}

		protected function checkConfig(array $aConfig) {
			if(!isset($aConfig['name']) && !isset($aConfig['extend'])) {
				throw new MissingModelNameException('Model config need name to be specified. Use name => <ModelName> property. Alternatively can be used to extend existing model (use extend => <ModelName> property).');
			}
			if(isset($aConfig['extend']) && isset($aConfig['name'])) {
				throw new ConfigException('If you use extend property, you may not specify name property.');
			}
			$diff = array_diff($configKeys = array_keys($aConfig), self::$known);
			if(!empty($diff)) {
				throw new UnknownDirectiveException(sprintf('Unknown model config directive: [%s]".', implode(', ', $diff)));
			}
		}

		/**
		 * sestaví celou konfiguraci modelu ze zadaného pole
		 *
		 * @param array $aConfig
		 *
		 * @throws MissingModelNameException
		 *
		 * @return $this
		 */
		public function config(array $aConfig) {
			$this->checkConfig($aConfig);
			if(isset($aConfig['extend'])) {
				$this->property('extend', $aConfig['extend']);
				$aConfig['name'] = $aConfig['extend'].'Ext';
			}
			$this->property('virtual', isset($aConfig['extend']) || (isset($aConfig['virtual']) && $aConfig['virtual'] === true));
			$this->property('name', $name = $aConfig['name']);
			$this->property('default', isset($aConfig['default']) ? $aConfig['default'] : array());
			if(isset($aConfig['comment'])) {
				$this->property('comment', $aConfig['comment']);
			}
			if(!$this->isVirtual()) {
				$this->addProperty('id', array(
					'primary' => true,
					'virtual' => true
				));
			}
			foreach($aConfig['property'] as $name => $config) {
				$this->addProperty($name, $config);
			}
			if(isset($aConfig['unique']) && is_array($aConfig['unique'])) {
				$this->configUnique($aConfig['unique']);
			}
			if(isset($aConfig['index']) && is_array($aConfig['index'])) {
				$this->configIndex($aConfig['index']);
			}
			if(isset($aConfig['bind']) && is_array($aConfig['bind'])) {
				$this->configBind($aConfig['bind']);
			}
			return $this;
		}

		/**
		 * @param string $aName
		 * @param array $aConfig
		 *
		 * @return Property
		 */
		protected function createProperty($aName, array $aConfig = null) {
			$property = new Property($aName);
			$property->config($aConfig);
			/**
			 * metoda dbá na nastavení výchozích hodnot
			 */
			$property->setDefault($this->getOrDefault('default', array()));
			return $property;
		}

		/**
		 * @param string|Property $aName
		 * @param array $aConfig
		 *
		 * @return Property
		 */
		public function addProperty($aName, array $aConfig = null) {
			$property = $aName;
			if(!($property instanceof Property)) {
				$property = $this->createProperty($aName, $aConfig);
			}
			$name = $property->getName();
			$this->propertyList[$name] = $property;
			if($property->isUnique()) {
				$this->uniqueList[$name] = $property;
			}
			if($property->isPrimary()) {
				$this->primartList[$name] = $property;
			}
			if(!$property->isNullable()) {
				$this->requiredList[$name] = $property;
			}
			if(!$this->isVirtual() && $property->isBind()) {
				$this->modelBindList[$property->getBind()] = $this->bindList[$name] = $property->property('virtual', true);
				$name = "{$name}_id";
				$this->propertyList[$name] = $this->bindList[$name] = $reference = $this->createProperty($name, array(
					'bind' => $property->getBind(),
					'virtual' => false,
					'nullable' => $property->isNullable(),
				));
				if(!$reference->isNullable()) {
					$this->requiredList[$name] = $reference;
				}
				if($property->hasComment()) {
					$reference->property('comment', $property->getComment());
				}
				$property->property('reference', $reference);
				$reference->property('reference', $property);
			}
			return $property;
		}

		public function getName() {
			return $this->get('name');
		}

		public function getSourceName() {
			return $this->getOrDefault('source', Strings::recamel($this->getName(), '_'));
		}

		/**
		 * @param string $aProperty
		 * @param mixed|null $aDefault
		 *
		 * @throws EmptyModelConfigException
		 *
		 * @return mixed
		 */
		public function get($aProperty = null, $aDefault = null) {
			try {
				return parent::get($aProperty);
			} catch(NoPropertiesException $e) {
				throw new EmptyModelConfigException('Current model config has not been configured (call ModelConfig::config() to configure or build it manually).');
			}
		}

		/**
		 * @return Property[]
		 */
		public function getPropertyList() {
			return $this->propertyList;
		}

		/**
		 * @return Property[]
		 */
		public function getPhysicalPropertyList() {
			$list = array();
			foreach($this->propertyList as $name => $property) {
				if($property->isVirtual()) {
					continue;
				}
				$list[$name] = $property;
			}
			return $list;
		}

		/**
		 * @param string $aName
		 *
		 * @throws PropertyException
		 *
		 * @return Property
		 */
		public function getProperty($aName) {
			$name = Strings::recamel($aName, '_');
			if(!isset($this->propertyList[$name])) {
				throw new PropertyException(sprintf('Unknown model [%s] property "%s".', $this->getName(), $name), $name);
			}
			return $this->propertyList[$name];
		}

		public function hasBindList() {
			return !empty($this->bindList);
		}

		public function getBindList() {
			return $this->bindList;
		}

		public function getBind($aBind) {
			$bind = Strings::recamel($aBind, '_');
			try {
				if(empty($this->bindList)) {
					throw new UnknownUniquePropertyException("Model '".$this->getName()."' has no bind properties.", $bind);
				}
				if(!isset($this->bindList[$bind])) {
					throw new UnknownBindPropertyException("Requested property '$bind' is not bind.", $bind);
				}
				return $this->bindList[$bind];
			} catch(UnknownBindPropertyException $e) {
				if(!isset($this->modelBindList[$aBind])) {
					throw new CannotFindBindException("Cannot find bind property for '$aBind'.");
				}
				return $this->modelBindList[$aBind];
			}
		}

		/**
		 * @return Property[]
		 */
		public function getPrimaryList() {
			return $this->primartList;
		}

		/**
		 * @return Property[]
		 */
		public function getRequiredList() {
			return $this->requiredList;
		}

		/**
		 * @throws UnknownUniquePropertyException
		 *
		 * @return Property
		 */
		public function getUnique() {
			if(empty($this->uniqueList)) {
				throw new UnknownUniquePropertyException(sprintf('Model %s has no unique properties. Unique groups are ignored.', $this->getName()));
			}
			return reset($this->uniqueList);
		}

		/**
		 * @return Property[]
		 */
		public function getUniqueList() {
			return $this->uniqueList;
		}

		public function addUniqueGroup(array $aGroup) {
			$this->uniqueGroup[] = $aGroup;
			return $this;
		}

		/**
		 * @return Property[][]
		 */
		public function getUniqueGroupList() {
			return $this->uniqueGroup;
		}

		public function addIndexGroup(array $aGroup) {
			$this->indexGroup[] = $aGroup;
			return $this;
		}

		/**
		 * @return Property[][]
		 */
		public function getIndexGroupList() {
			return $this->indexGroup;
		}

		/**
		 * @return Config[]
		 */
		public function getModelBindList() {
			return $this->bindModel;
		}

		/**
		 * vrátí MN vazby v podobě array(spojovací config, cílový config)
		 *
		 * @return Config[][]
		 */
		public function getMnList() {
			return $this->mn;
		}

		/**
		 * @return Config[]
		 */
		public function getBoundList() {
			return $this->bound;
		}

		public function hasComment() {
			return $this->has('comment');
		}

		public function getComment() {
			return $this->get('comment');
		}

		public function getExtend() {
			return $this->get('extend');
		}

		public function isExtension() {
			return $this->has('extend');
		}

		public function isVirtual() {
			return $this->getOrDefault('virtual', false);
		}

		/**
		 * háček, který se zavolá po načtení všech konfigurací
		 *
		 * @param ILoaderService $aLoaderService
		 */
		public function hookCompute(ILoaderService $aLoaderService) {
			foreach($this->bindModel as $modelConfig) {
				$aLoaderService->addModelConfig($modelConfig);
			}
		}

		/**
		 * háček, který se zavolá po načtení všech konfigurací a po dopočítání modelů; je určený pro dosazení vazeb (bindů)
		 *
		 * @param ILoaderService|Config[] $aLoaderService
		 */
		public function hookLoaded(ILoaderService $aLoaderService) {
			foreach($this->getBindList() as $bind) {
				$bind->property('bind', $aLoaderService->getModelConfig($bind->getBind()));
			}
			if(!$this->isVirtual()) {
				foreach($this->uniqueGroup as &$group) {
					foreach($group as &$item) {
						$item = $this->getProperty($item)->property('unique', true);
					}
				}
				foreach($this->indexGroup as &$group) {
					foreach($group as &$item) {
						$item = $this->getProperty($item)->property('index', true);
					}
				}
			}
			foreach($aLoaderService as $subConfig) {
				if($subConfig === $this) {
					continue;
				}
				foreach($subConfig->getBindList() as $subProperty) {
					if($subProperty->getBind() === $this && $subProperty->isVirtual()) {
						$this->bound[] = array(
							$subConfig,
							$subProperty
						);
						foreach($subConfig->getBindList() as $targetProperty) {
							if($subProperty === $targetProperty || !$targetProperty->isVirtual()) {
								continue;
							}
							$this->mn[] = array(
								$subConfig,
								$targetProperty->getBind()
							);
						}
					}
				}
			}
			if($this->isExtension()) {
				$thisGuyWillBeExtended = $aLoaderService->getModelConfig($this->getExtend());
				foreach($this->propertyList as $property) {
					$thisGuyWillBeExtended->addProperty($property);
				}
				foreach($this->uniqueGroup as $group) {
					$thisGuyWillBeExtended->addUniqueGroup($group);
				}
				foreach($this->indexGroup as $group) {
					$thisGuyWillBeExtended->addIndexGroup($group);
				}
			}
		}
	}
