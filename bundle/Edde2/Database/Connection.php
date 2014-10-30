<?php
	namespace Edde2\Database;

	use Edde2\Reflection\IClassLoader;
	use Edde2\Utils\Strings;
	use Edde2\Utils\TreeObjectEx;
	use Nette\Database\Connection as NetteConnection;
	use Nette\Database\Reflection\ConventionalReflection;
	use Nette\Database\ResultSet;

	class Connection extends NetteConnection {
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * aktuální transakce; pokud běží, další createTransaction vyhodí výjimku
		 *
		 * @var ITransaction
		 */
		private $transaction;

		public function __construct(TreeObjectEx $aConfig, IClassLoader $aClassLoader) {
			$this->classLoader = $aClassLoader;
			$config = $aConfig;
			$dsn = $config->getOrDefault('dsn', sprintf('%s:dbname=%s;host=%s', $config->get('driver'), $config->get('database'), $config->getOrDefault('host', '127.0.0.1')));
			/** @var $options TreeObjectEx */
			$options = $config->getOrDefault('options', new TreeObjectEx(array()));
			if(($driverClass = $options->getOrDefault('driverClass')) === null) {
				$driverClass = sprintf('Edde2\\Database\\Drivers\\%sDriver', Strings::firstUpper($config->get('driver')));
			}
			$options->property('driverClass', $driverClass);
			parent::__construct($dsn, $config->getOrDefault('user'), $config->getOrDefault('password'), $options->current());
			Helpers::createDebugPanel($this, false);
		}

		public function getDriverName() {
			/** PDO se nepouziva, protoze automaticky vytvori pripojeni do databaze; a to fakt nechci */
			$dsn = explode(':', $this->getDsn());
			return $dsn[0];
		}

		/**
		 * @deprecated
		 *
		 * @param string $aStatement
		 * @param array $aParams
		 *
		 * @throws ColumnNotFoundException
		 * @throws DatabaseException
		 * @throws TableNotFoundException
		 * @return ResultSet
		 */
		public function queryArgs($aStatement, array $aParams) {
			try {
				return parent::queryArgs($aStatement, $aParams);
			} catch(\PDOException $e) {
				throw $this->getSupplementalDriver()->exception($e);
			}
		}

		/**
		 * @param string $aStatement
		 * @param array $aParams
		 *
		 * @return ResultSet
		 */
		public function createResultSet($aStatement, array $aParams = array()) {
			try {
				$result = new ResultSet($this, $aStatement, $aParams);
				$this->onQuery($this, $result);
				return $result;
			} catch(\PDOException $e) {
				$this->onQuery($this, $e);
				throw $e;
			}
		}

		/**
		 * vytvoří databázový kontext pro dotazy/vytváření modelů
		 *
		 * @return Context
		 */
		public function context() {
			/**
			 * takto je to nutné, protože třída má funkce pro inject
			 */
			return $this->classLoader->create(Context::getReflection()->getName(), array(
				$this,
				new ConventionalReflection('id', '%s'),
				null
			));
		}

		/**
		 * vytvoří novou transakci a hlídá, zda na toto spojení je použita právě jedna
		 *
		 * @throws NestedTransactionException
		 * @return ITransaction
		 */
		public function createTransaction() {
			if($this->transaction !== null) {
				throw new NestedTransactionException('Cannot create nested transaction. If you want current transaction, use Connection::getTransaction().', $this->transaction);
			}
			return $this->transaction = new Transaction($this);
		}

		/**
		 * @return DummyTransaction
		 */
		public function createDummyTransaction() {
			return new DummyTransaction();
		}

		/**
		 * bezpěčně vytvoří a vrátí transakci - nezáleží na jejím stavu
		 *
		 * @return ITransaction
		 */
		public function getTransaction() {
			if($this->transaction === null) {
				$this->createTransaction();
			}
			return $this->transaction;
		}

		/**
		 * uvolní již dokončenou transakci; metoda je primárně pro vnitřní volání ze samotná transakce (automatický úklid)
		 *
		 * @return $this
		 *
		 * @throws NoTransactionException
		 * @throws RunningTransactionException
		 */
		public function releaseTransaction() {
			if($this->transaction === null) {
				throw new NoTransactionException('Current connection has no transaction.', null);
			}
			if($this->transaction->isRunning()) {
				throw new RunningTransactionException('Cannot release started transaction. Use this exception to get transaction.', $this->transaction);
			}
			$this->transaction = null;
			return $this;
		}

		/**
		 * funkce přijímá dynamický počet parametrů, které se předají danému callbacku
		 *
		 * @param callback $aCallback
		 *
		 * @return mixed
		 *
		 * @throws \Exception
		 */
		public function transaction($aCallback) {
			return $this->createTransaction()->run($aCallback, array_slice(func_get_args(), 1));
		}

		/**
		 * @return Query
		 */
		public function createQuery() {
			return $this->classLoader->create(Query::getReflection()->getName(), array($this));
		}

		/**
		 * zkratka pro volání ISupplementalDriver::delimite
		 *
		 * @param string $aString
		 *
		 * @return string
		 */
		public function delimite($aString) {
			return $this->getSupplementalDriver()->delimite($aString);
		}

		/**
		 * @return ISupplementalDriver
		 */
		public function getSupplementalDriver() {
			return parent::getSupplementalDriver();
		}
	}
