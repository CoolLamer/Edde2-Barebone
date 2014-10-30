<?php
	namespace Edde2\Model2\Config;

	interface ILoaderService extends \IteratorAggregate {
		public function build();

		/**
		 * vrátí konfiguraci daného modelu
		 *
		 * @param string $aName
		 *
		 * @return Config
		 */
		public function getModelConfig($aName);

		/**
		 * @return Config[]
		 */
		public function getConfigList();

		public function addModelConfig(Config $aModelConfig);
	}
