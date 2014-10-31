<?php
	namespace Edde2\Bootstrap;

	use Edde2\DI\Container;
	use Edde2\Reflection\EddeLoader;
	use Edde2\Security\User;
	use Edde2\Utils\Arrays;
	use Nette\Caching\Storages\FileStorage;
	use Nette\Configurator as NetteConfigurator;
	use Nette\Object;
	use Nette\Utils\Finder;

	/**
	 * Upgradovaná verze konfigurátoru, která přidává a ošetřuje některé drobnosti, které v původním
	 * konfigurátoru schází; hlavním rozdílem je možnost nasetovat jednotlivé adrsáře, díky čemuž se
	 * v systému eliminuje nutkání používat adresářové konstanty.
	 *
	 * Také se stará o vytvoření vlastního systémového kontejneru, který obaluje původní Nettí a doplňuje
	 * některé věci, které původně schází.
	 */
	class Configurator extends NetteConfigurator {
		private $dirs = array();
		/**
		 * @var EddeLoader
		 */
		private $eddeLoader;
		private $debugMode;
		/**
		 * @var Container
		 */
		private $context;

		public function __construct($aRootDir) {
			parent::__construct();
			$this->setRootDir($aRootDir);
		}

		public function setRootDir($aRoot) {
			$dirz = array(
				'root' => $aRoot,
				'lib' => $aRoot.'/lib',
				'web' => $aRoot.'/www',
				'app' => ($appDir = $aRoot.'/app'),
				'temp' => ($tempDir = $aRoot.'/temp'),
				'cache' => $tempDir.'/cache',
				'log' => $aRoot.'/logs',
				'config' => $appDir.'/config',
			);
			$alternatives = array(
				'log' => 'log',
			);
			foreach($alternatives as $type => $value) {
				if(realpath($dirz[$type]) === false) {
					$dirz[$type] = realpath($aRoot.'/'.$value);
				}
			}
			$this->dirs = array_filter($dirz);
			return $this;
		}

		public function setDebugMode($aDebugMode) {
			$this->debugMode = $aDebugMode;
			return $this;
		}

		public function disableDebugMode() {
			$this->debugMode = false;
			return $this;
		}

		protected function loadConfigPath($aPath) {
			$configDir = $this->dirs['cache'].'/config';
			$configCacheFile = $configDir.'/config-cache';
			if(file_exists($configCacheFile)) {
				foreach(preg_split('~\R~', file_get_contents($configCacheFile)) as $config) {
					$this->addConfig($config, self::AUTO);
				}
				return;
			}
			@mkdir($configDir, 0750, true);
			$configFilez = array();
			foreach(Finder::find('*.config.neon')->from($aPath) as $path => $info) {
				$configFilez[] = $path;
			}
			if(($eddeBundle = \Phar::running()) !== '') {
				foreach(new \RecursiveIteratorIterator(new \Phar($eddeBundle)) as $info) {
					if(strrpos($info->getFileName(), '.config.neon') !== false) {
						file_put_contents($configFile = ($configDir.'/'.($config = sha1($info->getPathname())).'.neon'), file_get_contents($info->getPathname()));
						$configFilez[] = $configFile;
					}
				}
			}
			foreach($configFilez as &$file) {
				$file = realpath($file);
				$this->addConfig($file, self::AUTO);
			}
			file_put_contents($configCacheFile, implode("\n", $configFilez));
		}

		public function createRobotLoader() {
			$loader = new EddeLoader(new FileStorage($this->getCacheDirectory()));
			$loader->addDirectory($this->dirs['lib']);
			$loader->addDirectory($this->dirs['app']);
			$loader->register();
			return $this->eddeLoader = $loader;
		}

		protected function registerLoader(Container $aContext) {
			$parameters = $aContext->getParameters();
			$loader = $this->eddeLoader;
			if(isset($parameters['loader'])) {
				foreach(Arrays::forceArray($parameters['loader']) as $dir) {
					$loader->addDirectory($dir);
				}
			}
			$loader->register();
		}

		protected function setupDebugMode() {
			if($enableDebug = file_exists($localConfig = $this->dirs['config'].'/config.local.neon')) {
				$this->addConfig($localConfig);
			} else if($this->isDebugMode() && ($enableDebug = file_exists($develConfig = $this->dirs['config'].'/development.neon'))) {
				$this->addConfig($develConfig);
			}
			if($this->debugMode !== null) {
				$enableDebug = $this->debugMode;
			}
			$this->setDebugMode($enableDebug);
			if($this->debugMode === null || $this->debugMode !== false) {
				$this->enableDebugger($this->dirs['log']);
			}
			return $this;
		}

		/**
		 * @return Container
		 */
		public function build() {
			$this->parameters = $this->getDefaultParameters();
			$this->setTempDirectory($this->dirs['temp']);
			$this->loadConfigPath($this->dirs['lib']);
			$this->addConfig($this->dirs['config'].'/config.neon', self::AUTO);
			$this->setupDebugMode();
			$this->createRobotLoader();
			/** @var $context Container */
			$context = $this->createContainer();
			$this->setupExtensions($context);
			$context->addService('loader', $this->eddeLoader);
			$this->registerLoader($context);
			/**
			 * pokud aplikace běží na OPcache, je potřeba ji resetovat, pokud se aplikace debugguje, jinak se nerefreshnout skripty
			 */
			if(extension_loaded('Zend OPcache') && $this->isDebugMode()) {
				opcache_reset();
			}
			return $this->context = $context;
		}

		/**
		 * shortcut funkce pro vytvoreni aplikace a spusteni (getService('application')->run())
		 */
		public function run() {
			$this->build();
			$this->context->getService('application')->run();
		}

		protected function getDefaultParameters() {
			$defaults = parent::getDefaultParameters();
			if(!isset($this->dirs['app'])) {
				return $defaults;
			}
			$defaults['container'] = array(
				'class' => 'SystemContainer',
				'parent' => Container::getReflection()->getName(),
			);
			$defaults['appDir'] = $this->dirs['app'];
			$defaults['logDir'] = $this->dirs['log'];
			$defaults['wwwDir'] = $this->dirs['web'];
			$defaults['libDir'] = $this->dirs['lib'];
			$defaults['tempDir'] = $this->dirs['temp'];
			$defaults['cacheDir'] = $this->dirs['cache'];
			$defaults['rootDir'] = $this->dirs['root'];
			return $defaults;
		}

		protected function setupExtensions(Container $aContext) {
			Object::extensionMethod('cacheId', function (Object $aSource, $aId) {
				return array(
					get_class($aSource),
					$aId
				);
			});
			/**
			 * @var $user User
			 */
			$user = $aContext->getService('user');
			Object::extensionMethod('acl', function (Object $aSource, $aResource, $aPermission = null, $aNeed = true) use ($user) {
				return $user->can($aResource, $aPermission, $aNeed);
			});
		}
	}
