<?php
	namespace Edde2\Model2\Import;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Database\Connection;
	use Edde2\Database\Helpers;
	use Edde2\FileSystem\CsvFile;
	use Edde2\FileSystem\FileSystem;
	use Edde2\FileSystem\TextFile;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Templating\TemplateLoader;
	use Edde2\Utils\Strings;
	use Nette\Database\Table\Selection;
	use Nette\Neon\Neon;
	use Nette\Utils\Random;

	class CsvImportService extends ImportService {
		private $path;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var Selection[]
		 */
		private $tables;

		public function __construct($aPath, ILoaderService $aLoaderService, Connection $aConnection, TemplateLoader $aTemplateLoader, CommonConfig $aCommonConfig) {
			parent::__construct($aLoaderService, $aConnection, $aTemplateLoader);
			$this->path = $aPath;
			$this->commonConfig = $aCommonConfig;
		}

		protected function hookImport() {
			$connection = $this->connection();
			$context = $connection->context();
			$path = $this->commonConfig->getTempDir();
			$sqlFile = new TextFile(sprintf('%s/fixtures-%s.sql', $path, Random::generate()));
			$sqlFile->openForWrite();
			$config = array();
			foreach(FileSystem::fetch($this->path, '*.csv') as $file => $info) {
				$csvFile = new CsvFile($file);
				$csvFile->openForRead();
				$modelName = $csvFile->read();
				$modelName = reset($modelName);
				$aProperties = $csvFile->read();
				/** while je použitý úmyslně - podpora foreache resetuje soubor na začátek a opět by přečetl meta-data CSVčka (model, property, ...) */
				while($aLine = $csvFile->read()) {
					if(count($aProperties) !== count($aLine)) {
						throw new ImportException(sprintf('Loaded values [%s] did not match csv column definition [%s] (property count and values count are not equal).', implode(', ', $aProperties), implode(', ', $aLine)));
					}
					$modelConfig = isset($config[$modelName]) ? $config[$modelName] : ($config[$modelName] = $this->modelConfig($modelName));
					$tableName = $modelConfig->getSourceName();
					$update = array();
					$insert = array();
					foreach(array_combine($aProperties, $aLine) as $name => $value) {
						if(!is_numeric($value) && empty($value)) {
							continue;
						}
						$property = $modelConfig->getProperty($name);
						if($property->isBind()) {
							$query = $connection->createQuery();
							$sourceName = $property->getBind()->getSourceName();
							$referenceName = $property->getReference()->getName();
							$query->table($sourceName)->select('id');
							$argz = array();
							foreach(Neon::decode($value) as $where => $v) {
								$query->whereAnd()->eq(array(
									$sourceName,
									$where
								), ":$where");
								$argz[":$where"] = $v;
							}
							$query->argz($argz);
							$update[] = $connection->delimite($referenceName).' = ('.$query->sql().')';
							$insert[$referenceName] = null;
						} else if(($source = Strings::match($value, '~^file://(.+)$~')) !== null) {
							$insert[$name] = file_get_contents(dirname($file).'/'.end($source));
						} else {
							$insert[$name] = $value;
						}
					}
					$table = null;
					if(isset($this->tables[$tableName])) {
						$table = $this->tables[$tableName];
					} else {
						$this->tables[$tableName] = $table = $context->table($tableName);
					}
					$row = $table->insert($insert);
					if(count($update) > 0) {
						$sqlFile->write(sprintf('UPDATE %s SET %s WHERE %s = %d;', $connection->delimite($tableName), implode(', ', $update), $connection->delimite('id'), $row->getPrimary()));
					}
				}
			}
			$sqlFile->close();
			/**
			 * trikový blok, který se stará o dovázání chybějících vazeb (tzn. všechny modely jsou již uložené, nyní je možné v databázi vyhledávat...)
			 */
			Helpers::loadFromFile($connection, $sqlFile->getFullFileName());
		}
	}
