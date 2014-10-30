<?php
	namespace Edde2\Database;

	use Edde2\Query\Query2;
	use Nette\Database\ResultSet;

	/**
	 * @db
	 */
	class Query extends Query2 implements \IteratorAggregate {
		/**
		 * @var Connection
		 */
		private $connection;
		/**
		 * @var callback
		 */
		private $delimite;
		/**
		 * @var array
		 */
		private $argz = array();

		public function __construct(Connection $aConnection) {
			$this->connection = $aConnection;
		}

		public function hookConstruct() {
			$this->delimite = array(
				$this->connection->getSupplementalDriver(),
				'delimite'
			);
		}

		/**
		 * @param null $aEscapeCallback parametr není využit
		 *
		 * @return string
		 */
		public function format($aEscapeCallback = null) {
			return parent::format($this->delimite);
		}

		public function sql($aEscapeCallback = null) {
			$sql = $this->format($aEscapeCallback);
			foreach($this->argz as $arg => $value) {
				$sql = str_replace($arg, $this->connection->quote($value), $sql);
			}
			return $sql;
		}

		/**
		 * @param array $aArgz
		 *
		 * @return ResultSet
		 * @throws DatabaseException
		 */
		public function query(array $aArgz = array()) {
			return $this->connection->createResultSet($this->format(), !empty($aArgz) ? $aArgz : $this->argz);
		}

		/**
		 * nastaví parametry dotazu pro případ použítí jako iterovatelnost
		 *
		 * @param array $aArgz
		 *
		 * @return $this
		 */
		public function argz(array $aArgz) {
			$this->argz = array_merge($this->argz, $aArgz);
			return $this;
		}

		public function getArgz() {
			return $this->argz;
		}

		public function getIterator() {
			return $this->query($this->argz);
		}
	}

