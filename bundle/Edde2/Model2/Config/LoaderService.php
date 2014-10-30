<?php
	namespace Edde2\Model2\Config;

	use Edde2\Caching\Cache;
	use Edde2\Object;
	use Edde2\Utils\Arrays;
	use Edde2\Utils\Strings;

	abstract class LoaderService extends Object implements ILoaderService {
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var Config[]
		 */
		private $modelConfig = array();
		private $enableConfigCache = true;

		public function __construct(Cache $aCache = null) {
			$this->cache = $aCache;
		}

		/**
		 * zakáže načítání konfigurace z cache (ve fázi build); tím se vynutí pokaždé volání self::load()
		 *
		 * @return $this
		 */
		protected function disableConfigCache() {
			$this->enableConfigCache = false;
			return $this;
		}

		public function putModelConfig(array $aModelConfig) {
			$modelConfig = new Config();
			$modelConfig->config($aModelConfig);
			$this->addModelConfig($modelConfig);
			return $this;
		}

		public function addModelConfig(Config $aModelConfig) {
			$this->modelConfig[$aModelConfig->getName()] = $aModelConfig;
			return $this;
		}

		public function hasModelConfig() {
			return !empty($this->modelConfig);
		}

		/**
		 * @return bool
		 */
		protected function loadFromCache() {
			return ($this->enableConfigCache === true) && ($this->modelConfig = $this->cache->load($this->cacheId('config-list'))) !== null;
		}

		protected function saveToCache() {
			if($this->enableConfigCache === true) {
				$this->cache->save($this->cacheId('config-list'), $this->modelConfig);
			}
			return $this;
		}

		protected function getModelName($aName) {
			if(($name = $this->cache->load($cacheId = $this->cacheId('config-name-'.$aName))) !== null) {
				if($name === false) {
					throw new UnknownModelConfigException(sprintf('Cannot find model with name "%s". ILoaderService::getModelName() from cache === false.', $aName), $aName);
				}
				return $name;
			}
			$name = Strings::pregString(Strings::camelize($aName));
			$keys = preg_grep("~^$name$~", array_keys($this->modelConfig));
			if(($modelName = reset($keys)) === false) {
				$this->cache->save($cacheId, false);
				throw new UnknownModelConfigException(sprintf('Cannot find model with name "%s" [%s].', $aName, $name), $name);
			}
			return $this->cache->save($cacheId, $modelName);
		}

		final public function build() {
			if($this->hasModelConfig()) {
				return $this;
			}
			if($this->loadFromCache()) {
				return $this;
			}
			$this->load();
			$this->modelConfig = Arrays::forceArray($this->modelConfig);
			foreach($this->modelConfig as $config) {
				$config->hookCompute($this);
			}
			foreach($this->modelConfig as $config) {
				$config->hookLoaded($this);
			}
			return $this;
		}

		/**
		 * vrátí konfiguraci daného modelu
		 *
		 * @param string $aName
		 *
		 * @return Config
		 */
		public function getModelConfig($aName) {
			$this->build();
			/**
			 * isset zde není potřeba, protože getModelName vyhledá jméno, pokud ne, vybouchne
			 */
			return $this->modelConfig[$this->getModelName($aName)];
		}

		/**
		 * @return Config[]
		 */
		public function getConfigList() {
			$this->build();
			return $this->modelConfig;
		}

		public function getIterator() {
			$this->build();
			return new \ArrayIterator($this->modelConfig);
		}

		abstract protected function load();
	}
