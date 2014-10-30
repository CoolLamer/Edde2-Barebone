<?php
	namespace Edde2\Api\Server;

	use Edde2\Bootstrap\Config;
	use Nette\DI\Container;

	class ServerConfig extends Config {
		public function build(array $aProperties = null) {
			if(!isset($aProperties['api'])) {
				return $this;
			}
			return parent::build($aProperties['api']);
		}

		public function getServiceList() {
			return $this->getOrDefault('service', array());
		}
	}
