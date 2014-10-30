<?php
	namespace Edde2\Sanitizer;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Caching\Cache;
	use Edde2\Neon\Neon;
	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Utils\TreeObjectEx;
	use Nette\Neon\Entity;
	use Nette\Utils\Finder;

	class SanitizerLoaderService extends Object {
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var TreeObjectEx[]
		 */
		private $config;

		public function __construct(CommonConfig $aCommonConfig, IClassLoader $aClassLoader, Cache $aCache) {
			$this->commonConfig = $aCommonConfig;
			$this->classLoader = $aClassLoader;
			$this->cache = $aCache;
		}

		public function load() {
			if($this->config !== null) {
				return array();
			}
			$sanitizer = array();
			if(($this->config = $this->cache->load($cacheId = $this->cacheId('config'))) === null) {
				foreach(Finder::findFiles('*.sanitizer')->from($this->commonConfig->getRootDir()) as $path => $info) {
					$config = new TreeObjectEx(Neon::decode($path));
					$this->config[$config->get('name')] = $config;
				}
				$this->cache->save($cacheId, $this->config);
			}
			/**
			 * jednotlivé sanitizátory je nutné vždy vytvořit ručně, protože není známo, jaké mají závislosti a zda jsou serializovatelné (kešovatelné)
			 */
			foreach($this->config as $config) {
				$sanitizer[$config->get('name')] = $this->create($config);
			}
			return $sanitizer;
		}

		/**
		 * @param string $aClazz
		 *
		 * @throws UnknownFilterTypeException
		 *
		 * @return IFilter
		 */
		protected function createSanitizer($aClazz) {
			$clazz = $aClazz instanceof Entity ? $aClazz->value : $aClazz;
			$argz = $aClazz instanceof Entity ? $aClazz->attributes : array();
			$class = $this->classLoader->create($clazz, $argz);
			if(!($class instanceof IFilter)) {
				throw new UnknownFilterTypeException(sprintf('Given class [%s] is not instanceof IFilter. Sanitizer filter must implement IFilter interface.', $clazz));
			}
			return $class;
		}

		protected function create(TreeObjectEx $aSanitizerConfig) {
			$sanitizer = new Sanitizer();
			foreach($aSanitizerConfig->get('rule') as $ruleName => $sanitizers) {
				$rule = new SanitizerRule();
				foreach($sanitizers as $class) {
					$rule->register($this->createSanitizer($class));
				}
				$sanitizer->register($ruleName, $rule);
			}
			return $sanitizer;
		}
	}
