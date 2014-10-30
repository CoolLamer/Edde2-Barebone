<?php
	namespace Edde2\Application\Routers;

	use Edde2\Bootstrap\Config;

	class RouterConfig extends Config {
		public function build(array $aProperties = null) {
			if(!isset($aProperties['router'])) {
				return $this;
			}
			return parent::build($aProperties['router']);
		}
	}
