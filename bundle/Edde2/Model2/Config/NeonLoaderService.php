<?php
	namespace Edde2\Model2\Config;

	use Edde2\Caching\Cache;
	use Edde2\FileSystem\FileSystem;
	use Edde2\FileSystem\NeonFile;
	use Edde2\Model2\Holder\Config as HolderConfig;
	use Edde2\Utils\Arrays;

	class NeonLoaderService extends LoaderService {
		/**
		 * @var HolderConfig
		 */
		private $holderConfig;

		public function __construct(HolderConfig $aHolderConfig, Cache $aCache) {
			parent::__construct($aCache);
			$this->holderConfig = $aHolderConfig;
		}

		/**
		 * načte konfiguraci modelů ze zadané cesty
		 *
		 * @throws NoConfigurationFoundException
		 *
		 * @return $this
		 */
		protected function load() {
			$path = $this->holderConfig->getPath();
			try {
				foreach(FileSystem::fetch($path, $this->holderConfig->getMask()) as $neonFile => $info) {
					$neonFile = new NeonFile($neonFile);
					$this->putModelConfig($neonFile->getContents());
				}
			} catch(\UnexpectedValueException $e) {
				throw new NoConfigurationFoundException(sprintf('Cannot load any model config in given path [%s].', implode(', ', Arrays::forceArray($path))));
			}
		}
	}
