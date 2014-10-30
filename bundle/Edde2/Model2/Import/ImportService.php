<?php
	namespace Edde2\Model2\Import;

	use Edde2\Database\Connection;
	use Edde2\Database\Helpers;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Model2\Config\Config;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Object;
	use Edde2\Templating\TemplateLoader;

	abstract class ImportService extends Object implements IImportService {
		/**
		 * @var ILoaderService
		 */
		private $loaderService;
		/**
		 * @var Connection
		 */
		private $connection;
		/**
		 * @var TemplateLoader
		 */
		private $templateLoader;

		public function __construct(ILoaderService $aLoaderService, Connection $aConnection, TemplateLoader $aTemplateLoader) {
			$this->loaderService = $aLoaderService;
			$this->connection = $aConnection;
			$this->templateLoader = $aTemplateLoader;
		}

		/**
		 * @param string $aName
		 *
		 * @return Config
		 */
		protected function modelConfig($aName) {
			return $this->loaderService->getModelConfig($aName);
		}

		/**
		 * @return Connection
		 */
		protected function connection() {
			return $this->connection;
		}

		final public function import() {
			$this->hookBefore();
			$this->hookImport();
			$this->hookAfter();
		}

		private function createTemplate($aDriverName, $aName) {
			$templateArgz = array(
				'connection' => $this->connection,
				'loaderService' => $this->loaderService,
			);
			$template = $this->templateLoader->template(sprintf('Import/templates/%s/%s.latte', $aDriverName, $aName));
			$template->propertyAll($templateArgz);
			return $template;
		}

		protected function hookBefore() {
			$this->createTemplate($this->connection->getDriverName(), 'hook-before')->save($beforeTemplateFile = FileSystem::tempFileName());
			Helpers::loadFromFile($this->connection, $beforeTemplateFile);
		}

		protected function hookAfter() {
			$this->createTemplate($this->connection->getDriverName(), 'hook-after')->save($afterTemplateFile = FileSystem::tempFileName());
			Helpers::loadFromFile($this->connection, $afterTemplateFile);
		}

		protected abstract function hookImport();
	}
