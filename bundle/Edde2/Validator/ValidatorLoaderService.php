<?php
	namespace Edde2\Validator;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Caching\Cache;
	use Edde2\Neon\Neon;
	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Utils\TreeObjectEx;
	use Nette\Neon\Entity;
	use Nette\Utils\Finder;

	class ValidatorLoaderService extends Object {
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
			$validator = array();
			if(($this->config = $this->cache->load($cacheId = $this->cacheId('config'))) === null) {
				foreach(Finder::findFiles('*.validator')->from($this->commonConfig->getRootDir()) as $path => $info) {
					$config = new TreeObjectEx(Neon::decode($path));
					$this->config[$config->get('name')] = $config;
				}
				$this->cache->save($cacheId, $this->config);
			}
			/**
			 * jednotlivé sanitizátory je nutné vždy vytvořit ručně, protože není známo, jaké mají závislosti a zda jsou serializovatelné (kešovatelné)
			 */
			foreach($this->config as $config) {
				$validator[$config->get('name')] = $this->create($config);
			}
			return $validator;
		}

		/**
		 * @param string $aClazz
		 *
		 * @throws UnknownValidatorTypeException
		 *
		 * @return IValidator
		 */
		protected function createValidator($aClazz) {
			$clazz = $aClazz instanceof Entity ? $aClazz->value : $aClazz;
			$argz = $aClazz instanceof Entity ? $aClazz->attributes : array();
			$class = $this->classLoader->create($clazz, $argz);
			if(!($class instanceof IValidator)) {
				throw new UnknownValidatorTypeException(sprintf('Given class [%s] is not instanceof IValidator. Validator must implement IValidator.', $clazz));
			}
			return $class;
		}

		protected function create(TreeObjectEx $aSanitizerConfig) {
			$sanitizer = new ValidatorList();
			foreach($aSanitizerConfig->get('rule') as $ruleName => $sanitizers) {
				$rule = new ValidatorRule();
				foreach($sanitizers as $class) {
					$rule->register($this->createValidator($class));
				}
				$sanitizer->register($ruleName, $rule);
			}
			return $sanitizer;
		}
	}
