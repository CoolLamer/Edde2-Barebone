<?php
	namespace Edde2\Caching;

	use Edde2\Bootstrap\Config;

	class CacheConfig extends Config {
		public function build(array $aProperties = null) {
			if(!isset($aProperties['config'])) {
				return $this;
			}
			return parent::build($aProperties['config']);
		}

		public function getPrefix() {
			return $this->getOrDefault('prefix');
		}
	}
