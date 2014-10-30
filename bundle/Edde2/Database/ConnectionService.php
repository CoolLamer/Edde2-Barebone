<?php
	namespace Edde2\Database;

	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Utils\TreeObjectEx;

	/**
	 * Obecná třída, která se stará o vytvoření spojení na základě požadavku; umí držet vícenásobná spojení do různých databází.
	 *
	 * Původně tato třída vznikla za účelem merge (sloučení, synchronizace) produkční databáze s vývojovou.
	 */
	class ConnectionService extends Object {
		/**
		 * @var ConnectionConfig|TreeObjectEx[]
		 */
		private $connectionConfig;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var Connection[]
		 */
		private $connection;
		/**
		 * @var Connection
		 */
		private $default;

		public function __construct(ConnectionConfig $aConnectionConfig, IClassLoader $aClassLoader) {
			$this->connectionConfig = $aConnectionConfig;
			$this->classLoader = $aClassLoader;
		}

		/**
		 * přidá nové spojení
		 *
		 * @param array $aConnectionConfig
		 *
		 * @return $this
		 */
		public function putConfig(array $aConnectionConfig) {
			$this->connectionConfig->property(key($aConnectionConfig), reset($aConnectionConfig));
			return $this;
		}

		/**
		 * @param TreeObjectEx $aConnectionConfig
		 *
		 * @return Connection
		 */
		public function createConnection(TreeObjectEx $aConnectionConfig) {
			return $this->classLoader->create(Connection::getReflection()->getName(), array($aConnectionConfig));
		}

		/**
		 * @param string $aName
		 *
		 * @throws ConnectionNotFoundException
		 *
		 * @return Connection
		 */
		public function getConnection($aName = 'default') {
			if(isset($this->connection[$aName])) {
				return $this->connection[$aName];
			}
			if(!$this->connectionConfig->hasConnection($aName)) {
				throw new ConnectionNotFoundException(sprintf('Requested connection [%s] not found in current config. Did you specify it in connection section under %s key?', $aName, $aName));
			}
			return $this->connection[$aName] = $this->createConnection($this->connectionConfig->getConnection($aName));
		}

		/**
		 * @throws ConnectionNotFoundException
		 * @throws UnknownDefaultConnection
		 *
		 * @return Connection
		 */
		public function getDefaultConnection() {
			if($this->default !== null) {
				return $this->default;
			}
			foreach($this->connectionConfig as $name => $config) {
				if($config->getOrDefault('default', false) === true) {
					return $this->default = $this->getConnection($name);
				}
			}
			throw new UnknownDefaultConnection(sprintf('Requested default connection. No of available connections is not marked as default.'));
		}
	}
