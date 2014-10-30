<?php
	namespace EddeModule\EddeService;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Caching\Cache;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Model2\ModelService;
	use Edde2\Object;

	/**
	 * Klientská část služby pro deploy/upgrade aplikace; akce nad touto službou volá SandboxService (tzn. presenter předává volání do této služby).
	 *
	 * Je potřeba mít na paměti, že aplikace stále běží na stejném serveru, pod stejným document-rootem; klient-server je zde pouze z hlediska vyvolání akce (příprava dat)
	 * a vykonání logiky (tzn. zpracování požadavku cílovou aplikací).
	 */
	class EddeService extends Object {
		/**
		 * @var ModelService
		 */
		private $modelService;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var Cache
		 */
		private $cache;

		public function __construct(ModelService $aModelService, CommonConfig $aCommonConfig, Cache $aCache) {
			$this->modelService = $aModelService;
			$this->commonConfig = $aCommonConfig;
			$this->cache = $aCache;
		}

		public function deploy() {
			$this->rebuild();
		}

		public function rebuild() {
			$this->rebuildSources();
			$this->rebuildDatabase();
		}

		public function rebuildSources() {
			$sourceGenerator = $this->modelService->createSourceGenerator();
			$sourceGenerator->reload();
			$sourceGenerator->generate();
			$sourceGenerator = $this->modelService->createSourceGenerator($this->modelService->getEddeHolderName());
			$sourceGenerator->reload();
			$sourceGenerator->generate();
		}

		public function rebuildDatabase() {
			$databaseGenerator = $this->modelService->createDatabaseGenerator();
			$databaseGenerator->setRebuild(true);
			$databaseGenerator->generate();
			$databaseGenerator = $this->modelService->createDatabaseGenerator($this->modelService->getEddeHolderName());
			$databaseGenerator->generate();
		}

		public function upgrade() {
		}

		public function cleanCache() {
			$this->cache->clean(array(Cache::ALL));
			FileSystem::delete($this->commonConfig->getCacheDir());
			return $this;
		}
	}
