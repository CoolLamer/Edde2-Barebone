<?php
	namespace Edde2\Model2;

	use Edde2\Database\Connection;
	use Edde2\Database\ConnectionService;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Model2\Generator\DatabaseGenerator;
	use Edde2\Model2\Generator\SourceGenerator;
	use Edde2\Model2\Holder\Config;
	use Edde2\Model2\Holder\Holder;
	use Edde2\Model2\Import\CsvImportService;
	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Utils\ObjectEx;
	use Edde2\Utils\Strings;

	class ModelService extends Object {
		/**
		 * @var ModelServiceConfig|ObjectEx[]
		 */
		private $modelConfig;
		/**
		 * @var ConnectionService
		 */
		private $connectionService;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var Holder[]
		 */
		private $holderList;

		public function __construct(ModelServiceConfig $aModelConfig, ConnectionService $aConnectionService, IClassLoader $aClassLoader) {
			$this->modelConfig = $aModelConfig;
			$this->connectionService = $aConnectionService;
			$this->classLoader = $aClassLoader;
		}

		public function getAvailableHolders() {
			return $this->modelConfig->property();
		}

		/**
		 * @param string $aName
		 *
		 * @throws UnknownHolderException
		 *
		 * @return Config
		 */
		public function getHolderConfig($aName) {
			return $this->modelConfig->getConfig($aName);
		}

		/**
		 * @param string $aName
		 *
		 * @return ILoaderService
		 */
		public function createLoaderService($aName) {
			$config = $this->getHolderConfig($aName);
			return $this->classLoader->create($config->getLoaderService(), array($config), false, false);
		}

		/**
		 * @param string $aName
		 * @param bool $aAllowAccessor
		 *
		 * @return Holder
		 */
		public function createHolder($aName, $aAllowAccessor = true) {
			if(isset($this->holderList[$aName])) {
				return $this->holderList[$aName];
			}
			$holderConfig = $this->modelConfig->getConfig($aName);
			return $this->holderList[$aName] = $this->classLoader->create(sprintf('%s\Model\%sModelHolder', $holderConfig->getNamespace(), Strings::camelize($aName)), array(
				$holderConfig,
				$this->createLoaderService($aName),
				$this->getHolderConnection($holderConfig),
			), $aAllowAccessor, false);
		}

		/**
		 * @param string $aName
		 *
		 * @return SourceGenerator
		 */
		public function createSourceGenerator($aName = null) {
			if($aName === null) {
				$aName = $this->getAppHolderName();
			}
			return $this->classLoader->create(SourceGenerator::getReflection()->getName(), array(
				$this->createLoaderService($aName),
				$this->getHolderConfig($aName),
			), false, false);
		}

		/**
		 * @param string $aName
		 *
		 * @return DatabaseGenerator
		 */
		public function createDatabaseGenerator($aName = null) {
			if($aName === null) {
				$aName = $this->getAppHolderName();
			}
			return $this->classLoader->create(DatabaseGenerator::getReflection()->getName(), array(
				$holderConfig = $this->getHolderConfig($aName),
				$holderConnection = $this->getHolderConnection($holderConfig),
				$loaderService = $this->createLoaderService($aName),
				$this->classLoader->create(CsvImportService::getReflection()->getName(), array(
					$holderConfig->getImportPath(),
					$loaderService,
					$holderConnection,
				), false, false)
			), false, false);
		}

		public function getAppHolder($aAllowAccessor = true) {
			return $this->createHolder($this->modelConfig->getDefault()->getName(), $aAllowAccessor);
		}

		public function getEddeHolder($aAllowAccessor = true) {
			return $this->createHolder($this->modelConfig->getEdde()->getName(), $aAllowAccessor);
		}

		/**
		 * @return string
		 */
		public function getAppHolderName() {
			return $this->modelConfig->getDefault()->getName();
		}

		/**
		 * @return string
		 */
		public function getEddeHolderName() {
			return $this->modelConfig->getEdde()->getName();
		}

		/**
		 * @param Config $aConfig
		 *
		 * @return Connection
		 */
		protected function getHolderConnection(Config $aConfig) {
			return $aConfig->getConnection() === true ? $this->connectionService->getDefaultConnection() : $this->connectionService->getConnection($aConfig->getConnection());
		}
	}
