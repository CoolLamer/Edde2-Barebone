<?php
	namespace Edde2\Reflection;

	use Edde2\Caching\Cache;
	use Edde2\Object;
	use Edde2\Utils\Strings;
	use Nette\DI\Container;
	use Nette\Reflection\ClassType;

	/**
	 * @di
	 */
	class ClassLoader extends Object implements IClassLoader {
		/**
		 * @var Container
		 */
		private $container;
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var EddeLoader
		 */
		private $loader;

		public function __construct(Container $aContainer, Cache $aCache) {
			$this->container = $aContainer;
			$this->cache = $aCache;
		}

		public function injectLoader(EddeLoader $aLoader) {
			$this->loader = $aLoader;
			$this->rebuild();
		}

		public function rebuild() {
			$this->cache->cleanTagged(array('classloader'));
		}

		public function create($aClass, array $aArgz = array(), $aAlloweAccessor = true, $aResolveInterface = true) {
			$accessor = null;
			$cacheId = $this->cacheId("accessor-$aClass");
			if($aAlloweAccessor === true && ($accessor = ($this->cache->load($cacheId))) === true) {
				return $this->create("{$aClass}Accessor")->get();
			}
			try {
				if($aAlloweAccessor === true && $accessor !== false) {
					/**
					 * $this->find je zde úmyslně - pokud třídu nenajde, vyhodí výjimku a nedojde k zacyklení volání create
					 */
					$clazz = $this->create($this->find("{$aClass}Accessor"))->get();
					$this->cache->saveTagged($cacheId, true, array('classloader'));
					return $clazz;
				}
			} catch(FindException $e) {
				$this->cache->saveTagged($cacheId, false, array('classloader'));
			}
			$clazz = $this->find($aClass, $aResolveInterface);
			$instance = $this->container->createInstance($clazz, $aArgz);
			$this->container->callInjects($instance);
			$reflection = ClassType::from($instance);
			if($reflection->hasMethod('hookConstruct')) {
				$method = $reflection->getMethod('hookConstruct');
				$method->setAccessible(true);
				$method->invoke($instance);
			}
			return $instance;
		}

		/**
		 * řekne, zda je daná třída implementace zadaného interface; obojí se prožene findem, tzn. lze zadat část názvu třídy/regulár
		 *
		 * @param string $aClazz
		 * @param string $aInterface
		 *
		 * @throws MultipleClassFoundException
		 * @throws NotInterfaceException
		 * @throws NothingFoundException
		 *
		 * @return bool
		 */
		public function isImplementor($aClazz, $aInterface) {
			if(($bool = $this->cache->load($cacheId = $this->cacheId(array(
					$aClazz,
					$aInterface
				)))) !== null
			) {
				return $bool;
			}
			if(!$this->loader->isInterface($aInterface = $this->find($aInterface, false))) {
				throw new NotInterfaceException(sprintf('Given interface [%s] is not interface.', $aInterface));
			}
			return $this->cache->save($cacheId, $this->loader->isImplementor($this->find($aClazz, false), $aInterface));
		}

		public function find($aClass, $aResolveInterface = true) {
			if(($clazz = $this->cache->load($cacheId = $this->cacheId("clazz.$aClass"))) === null) {
				$class = Strings::pregString($aClass);
				$clazz = preg_grep("~{$class}$~i", $this->loader->getIndexList());
				$this->cache->saveTagged($cacheId, $clazz, array('classloader'));
			}
			switch(count($clazz)) {
				case 0:
					throw new NothingFoundException("Cannot find class by given filter '$aClass'.", $aClass);
				case 1:
					$clazz = reset($clazz);
					break;
				default:
					throw new MultipleClassFoundException("Multiple class found by '$aClass'; concretize search criteria. [".implode(', ', $clazz)."]", $aClass, $clazz);
			}
			if($aResolveInterface === true) {
				if($this->loader->isInterface($aClass)) {
					$this->cache->saveTagged($cacheId, $implementors = $this->loader->getImplementorList($aClass), array('classloader'));
					switch(count($implementors)) {
						case 0:
							throw new NothingFoundException(sprintf('Cannot find any instantiable implementor for interface [%s].', $aClass), $aClass);
						case 1:
							$clazz = reset($implementors);
							break;
						default:
							throw new MultipleClassFoundException(sprintf('Interface [%s] has more implementors (%s).', $aClass, implode(', ', $implementors)), $aClass, $implementors);
					}
				}
			}
			return $clazz;
		}
	}
