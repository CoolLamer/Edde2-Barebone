<?php
	namespace Edde2\Model2\Generator;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Database\Connection;
	use Edde2\Database\Helpers;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Model2\Holder\Config AS HolderConfig;
	use Edde2\Model2\Import\IImportService;
	use Edde2\Object;
	use Edde2\Templating\TemplateLoader;

	class DatabaseGenerator extends Object {
		/**
		 * @var HolderConfig
		 */
		private $holderConfig;
		/**
		 * @var Connection
		 */
		private $connection;
		/**
		 * @var ILoaderService
		 */
		private $loaderService;
		/**
		 * @var IImportService
		 */
		private $importService;
		/**
		 * @var TemplateLoader
		 */
		private $templateLoader;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * možnost potlačit vytvoření vnitřní transakce
		 *
		 * @var bool
		 */
		private $suppressTransaction = false;
		/**
		 * pokud je true, celá databáze se zahodí a znovu se vytvoří; pokud je false, dojde k pokusu o dovytvoření tabulek
		 *
		 * @var bool
		 */
		private $rebuild = false;

		public function __construct(HolderConfig $aHolderConfig, Connection $aConnection, ILoaderService $aLoaderService, IImportService $aImportService, TemplateLoader $aTemplateLoader, CommonConfig $aCommonConfig) {
			$this->holderConfig = $aHolderConfig;
			$this->connection = $aConnection;
			$this->loaderService = $aLoaderService;
			$this->importService = $aImportService;
			$this->templateLoader = $aTemplateLoader;
			$this->commonConfig = $aCommonConfig;
		}

		public function setSuppressTransaction($aSuppressTransaction = true) {
			$this->suppressTransaction = (bool)$aSuppressTransaction;
			return $this;
		}

		public function setRebuild($aRebuild = true) {
			$this->rebuild = (bool)$aRebuild;
			return $this;
		}

		/**
		 * zahodí a znovu sestaví databázi
		 */
		public function generate() {
			$driverName = $this->connection->getDriverName();
			$templateArgz = array(
				'loaderService' => $this->loaderService,
				'rebuild' => $this->rebuild,
				'connection' => $this->connection,
				'driver' => $this->connection->getSupplementalDriver(),
			);
			$transaction = $this->suppressTransaction === false ? $this->connection->createTransaction() : $this->connection->createDummyTransaction();
			$transaction->begin();
			try {
				$hash = sprintf('%s', $this->holderConfig->getName());
				$path = sprintf('%s/database/%s', $this->commonConfig->getTempDir(), date('Y-m-d H.i'));
				FileSystem::createDir($path);
				$structureTemplate = $this->templateLoader->template(sprintf('/templates/%s/structure/structure.latte', $driverName));
				$structureTemplate->propertyAll($templateArgz);
				$structureTemplate->save($sql = sprintf('%s/%s-structure.sql', $path, $hash));
				/**
				 * první fáze importu - nacucání struktury databáze - veškeré constrainty jsou vypnuté (null, vzdálené klíče, duplikáty, ...)
				 */
				Helpers::loadFromFile($this->connection, $sql);
				/** keep it stupid simple - klasické nabrání dat a uložení modelů; v transakci jedna báseň */
				$this->importService->import();
				$constraintsTemplate = $this->templateLoader->template(sprintf('/templates/%s/constraints/constraints.latte', $driverName));
				$constraintsTemplate->propertyAll($templateArgz);
				$constraintsTemplate->save($sql = sprintf('%s/%s-constraints.sql', $path, $hash));
				/**
				 * poslední fáze - zapnutí veškerých constraint a případné post akce nad databází; vše je na svém místě a musí klapnout, jinak kaboom...!
				 */
				Helpers::loadFromFile($this->connection, $sql);
				$transaction->commit();
			} catch(\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		}
	}
