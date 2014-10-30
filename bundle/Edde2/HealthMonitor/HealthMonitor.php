<?php
	namespace Edde2\HealthMonitor;

	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;

	/**
	 * Služba určená pro sledování běhu aplikace a reportování časů případně dalších užitečných informací.
	 */
	class HealthMonitor extends Object {
		private static $instance;
		/**
		 * @var IClassLoader
		 */
		private static $classLoader;
		/**
		 * @var Timer[]
		 */
		private $timer = array();
		/**
		 * @var Stopwatch[]
		 */
		private $stopwatch = array();
		/**
		 * @var ServiceInfo
		 */
		private $serviceInfo = array();
		/**
		 * @var Logger[]
		 */
		private $logger = array();

		private static function getInstance() {
			if(self::$instance !== null) {
				return self::$instance;
			}
			return self::$instance = new self;
		}

		public static function injectClassLoader(IClassLoader $aClassLoader) {
			self::$classLoader = $aClassLoader;
		}

		/**
		 * @param string $aName
		 * @param string $aGroup
		 * @param float $aEpsilon
		 * @param bool $aAutostart
		 *
		 * @return Timer
		 */
		public static function timer($aName, $aGroup = 'common', $aEpsilon = null, $aAutostart = true) {
			$self = self::getInstance();
			if(isset($self->timer[$aGroup][$aName])) {
				return $self->timer[$aGroup][$aName];
			}
			return $self->timer[$aGroup][$aName] = new Timer($aEpsilon, $aAutostart);
		}

		/**
		 * @param string|null $aGroup
		 *
		 * @return Timer[][]
		 */
		public static function timerList($aGroup = 'common') {
			$self = self::getInstance();
			if($aGroup === null) {
				return $self->timer;
			}
			return $self->timer[$aGroup];
		}

		/**
		 * @param string $aName
		 * @param bool $aAutostart
		 *
		 * @return Stopwatch|Delta[]
		 */
		public static function stopwatch($aName, $aAutostart = true) {
			$self = self::getInstance();
			if(isset($self->stopwatch[$aName])) {
				return $self->stopwatch[$aName];
			}
			return $self->stopwatch[$aName] = new Stopwatch($aAutostart);
		}

		public static function serviceInfo($aName) {
			$self = self::getInstance();
			if(isset($self->serviceInfo[$aName])) {
				return $self->serviceInfo[$aName];
			}
			return $self->serviceInfo[$aName] = new ServiceInfo($aName, self::timer($aName, 'service'));
		}

		/**
		 * @return ServiceInfo[]
		 */
		public static function getServiceInfoList() {
			$self = self::getInstance();
			return $self->serviceInfo;
		}

		/**
		 * @param string|null $aName
		 * @param string|null $aClazz full-qualified-class-name
		 * @param array $aArgz
		 *
		 * @return FileLogger|ILogger
		 */
		public static function getLogger($aName, $aClazz = null, array $aArgz = array()) {
			$self = self::getInstance();
			if(isset($self->logger[$aName])) {
				return $self->logger[$aName];
			}
			return $self->logger[$aName] = self::$classLoader->create($aClazz ?: 'HealthMonitor\\FileLogger', array_merge(array($aName), $aArgz));
		}
	}
