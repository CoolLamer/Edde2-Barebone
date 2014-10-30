<?php
	namespace Edde2\Reflection;

	use Edde2\Object;
	use Nette\Caching\Cache;
	use Nette\Caching\IStorage;
	use Nette\NotSupportedException;

	class EddeLoader extends Object {
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var array
		 */
		private $dirz = array();
		/**
		 * index všech známých tříd
		 *
		 * @var MetaInfo[]
		 */
		private $clazzIndex = array();
		private $interfaceIndex = array();
		private $traitIndex = array();
		private $abstractIndex = array();

		public function __construct(IStorage $aStorage) {
			if(!extension_loaded('tokenizer')) {
				throw new NotSupportedException("PHP extension Tokenizer is not loaded.");
			}
			$this->cache = new Cache($aStorage, __CLASS__);
		}

		public function addDirectory($aDir) {
			$this->dirz[] = $aDir;
			return $this;
		}

		public function register() {
			spl_autoload_register(array(
				$this,
				'load'
			), true, true);
			$this->index($this->dirz);
		}

		protected function analyzeEntry(\SplFileInfo $aEntry) {
			$clazzList = array();
			if(!$aEntry->isFile()) {
				return $clazzList;
			}
			$extensions = array('php');
			if(!in_array($aEntry->getExtension(), $extensions)) {
				return $clazzList;
			}
			foreach($this->analyze(file_get_contents($aEntry->getPathname())) as $clazz => $meta) {
				$clazzList[$clazz] = new MetaInfo($meta[0], $aEntry->getPathname(), $meta[1], $meta[2]);
			}
			return $clazzList;
		}

		protected function analyzePharEntry(\SplFileInfo $aEntry) {
			$clazzList = array();
			foreach(new \RecursiveIteratorIterator(new \Phar($aEntry->getPathname()), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
				$clazzList = array_merge($clazzList, $this->analyzeEntry($entry));
			}
			return $clazzList;
		}

		public function index(array $aDirz, $aForce = false) {
			if(($this->clazzIndex = $this->cache->load($aDirz)) !== null && $aForce !== true) {
				$meta = $this->cache->load(array(
					$aDirz,
					'meta'
				));
				$this->interfaceIndex = $meta[0];
				$this->traitIndex = $meta[1];
				$this->abstractIndex = $meta[2];
				return $this->clazzIndex;
			}
			$this->clazzIndex = array();
			$exclude = null;
			foreach(array_unique($aDirz) as $dir) {
				foreach($this->createIterator($dir) as $entry) {
					if($entry->getFilename() === '.loader-ignore') {
						$exclude = $entry->getPath();
						continue;
					}
					if(strpos($entry->getPath(), $exclude) !== false) {
						continue;
					}
					$exclude = null;
					$entryList = array();
					switch($entry->getExtension()) {
						case 'phar':
							$entryList = $this->analyzePharEntry($entry);
							break;
						case 'php':
							$entryList = $this->analyzeEntry($entry);
							break;
					}
					$this->clazzIndex = array_merge($this->clazzIndex, $entryList);
				}
			}
			foreach($this->clazzIndex as $clazz => $meta) {
				switch($meta->getType()) {
					case T_INTERFACE:
						$this->interfaceIndex[$clazz] = $meta;
						break;
					case T_TRAIT:
						$this->traitIndex[$clazz] = $meta;
						break;
					case T_CLASS:
						if($meta->isAbstract()) {
							$this->abstractIndex[$clazz] = $meta;
						}
						break;
				}
				$reflaction = new \ReflectionClass($clazz);
				$meta->setInterfaceList($reflaction->getInterfaceNames());
				foreach($reflaction->getInterfaceNames() as $interface) {
					if(!isset($this->clazzIndex[$interface])) {
						continue;
					}
					$this->clazzIndex[$interface]->addImplementor($meta->getName());
				}
			}
			$this->cache->save(array(
				$aDirz,
				'meta'
			), array(
				$this->interfaceIndex,
				$this->traitIndex,
				$this->abstractIndex
			));
			return $this->cache->save($aDirz, $this->clazzIndex);
		}

		public function reindex() {
			$this->index($this->dirz, true);
		}

		public function load($aClazz) {
			if(!isset($this->clazzIndex[$aClazz]) || class_exists($aClazz, false)) {
				return $this;
			}
			/**
			 * lambda je použita z důvodu izolace oboru platnosti proměnných (eliminace přístupu k this/self z includovaných souborů)
			 */
			call_user_func(function ($file) {
				require($file);
			}, $this->clazzIndex[$aClazz]->getFile());
			return $this;
		}

		public function getIndex() {
			return $this->clazzIndex;
		}

		public function getIndexList() {
			return array_keys($this->clazzIndex);
		}

		public function getInterfaceIndex() {
			return $this->interfaceIndex;
		}

		public function getTraitIndex() {
			return $this->traitIndex;
		}

		public function getAbstractIndex() {
			return $this->abstractIndex;
		}

		/**
		 * @param string $aClazz
		 *
		 * @return bool
		 */
		public function isInterface($aClazz) {
			return isset($this->interfaceIndex[$aClazz]);
		}

		/**
		 * @param string $aClazz
		 * @param string $aInterface
		 *
		 * @return bool
		 */
		public function isImplementor($aClazz, $aInterface) {
			return $this->clazzIndex[$aClazz]->isImplementor($aInterface);
		}

		public function getImplementor($aClazz) {
			return $this->clazzIndex[$aClazz]->getImplementor();
		}

		public function getImplementorList($aClazz) {
			return $this->clazzIndex[$aClazz]->getImplementorList();
		}

		/**
		 * @param string $aSource
		 *
		 * @return MetaInfo[]
		 */
		public function analyze($aSource) {
			$metaInfo = array();
			$namespace = null;
			$name = null;
			$catch = -1;
			$abstract = false;
			foreach(@token_get_all($aSource) as $token) {
				$currentToken = $token;
				if(is_array($currentToken)) {
					$currentToken = reset($token);
				}
				switch($currentToken) {
					case T_WHITESPACE:
					case T_COMMENT:
					case T_DOC_COMMENT:
						continue 2;
					case T_NAMESPACE:
						$namespace = null;
						$catch = $currentToken;
						continue 2;
					case T_ABSTRACT:
						$abstract = true;
						continue 2;
					case ';':
					case '{':
						$catch = null;
						break;
					case T_CLASS:
					case T_INTERFACE:
					case T_TRAIT:
						$name = null;
						if(is_array($namespace)) {
							$namespace = implode('\\', $namespace);
						}
						$catch = $currentToken;
						continue 2;
					case T_STRING:
						switch($catch) {
							case T_NAMESPACE:
								$namespace[] = $token[1];
								break;
							case T_CLASS:
							case T_INTERFACE:
							case T_TRAIT:
								$name = $namespace.'\\'.$token[1];
								$metaInfo[$name] = array(
									$name,
									$catch,
									$abstract
								);
								$abstract = false;
								$catch = null;
						}
						continue 2;
				}
			}
			return $metaInfo;
		}

		/**
		 * @param string $aDir
		 *
		 * @return \RecursiveIteratorIterator|\SplFileInfo[]
		 */
		protected function createIterator($aDir) {
			if(!is_dir($aDir)) {
				return array();
			}
			return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aDir, \RecursiveDirectoryIterator::SKIP_DOTS));
		}
	}
