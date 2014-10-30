<?php
	namespace Edde2\Database;

	use Edde2\Bootstrap\Config;
	use Edde2\Utils\TreeObjectEx;

	class ConnectionConfig extends Config {
		public function build(array $aProperties = null) {
			if(!isset($aProperties['connection'])) {
				throw new ConfigNotFoundException('Requested database connection config; no connection configuration found - specify in section connection.');
			}
			if(!is_array(reset($aProperties['connection']))) {
				$default = $aProperties['connection'];
				$default['default'] = true;
				$aProperties['connection'] = array('default' => $default);
			}
			return parent::build($aProperties['connection']);
		}

		public function hasConnection($aName) {
			return $this->has($aName);
		}

		/**
		 * @param string $aName
		 *
		 * @return TreeObjectEx
		 */
		public function getConnection($aName) {
			return $this->get($aName);
		}
	}
