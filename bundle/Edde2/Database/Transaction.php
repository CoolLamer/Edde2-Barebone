<?php
	namespace Edde2\Database;

	use Edde2\Object;
	use Nette\Utils\Callback;

	class Transaction extends Object implements ITransaction {
		/**
		 * @var Connection
		 */
		private $connection;
		/**
		 * je transakce nastartovaná?
		 *
		 * @var bool
		 */
		private $open;

		public function __construct(Connection $aConnection) {
			$this->connection = $aConnection;
		}

		/**
		 * spustí transakci; pokud již běží, vyhodí výjimku
		 *
		 * @return $this
		 *
		 * @throws TransactionAlreadyStartedException
		 */
		public function begin() {
			if($this->open === true) {
				throw new TransactionAlreadyStartedException('Transaction has been already started.', $this);
			}
			$this->open = true;
			$this->connection->getPdo()->beginTransaction();
			return $this;
		}

		/**
		 * pomocná funkce, která zavře transakci - provede kontrolu běhu a uvolní transakci ze spojení
		 *
		 * @return $this
		 *
		 * @throws NoTransactionException
		 * @throws RunningTransactionException
		 * @throws StandByTransactionException
		 */
		protected function close() {
			if($this->open === false) {
				throw new StandByTransactionException('Transaction is not running.', $this);
			}
			$this->open = false;
			$this->connection->releaseTransaction();
			return $this;
		}

		/**
		 * potvrdí a ukončí transakci - kontroluje, zda běží
		 *
		 * @return $this
		 *
		 * @throws StandByTransactionException
		 */
		public function commit() {
			$this->close();
			$this->connection->getPdo()->commit();
			return $this;
		}

		/**
		 * vrátí změny spáchané v databázi a uvolní transakci - kontroluje, zda transakce běží
		 *
		 * @return $this
		 *
		 * @throws StandByTransactionException
		 */
		public function rollback() {
			$this->close();
			$this->connection->getPdo()->rollBack();
			return $this;
		}

		/**
		 * obalí zadaný callback transakcí; při úspěchu commitne, při neúspěchu vrátí změny a vyhodí vyvolanou výjimku
		 *
		 * @param callback $aCallback
		 * @param array $aArgs
		 *
		 * @return mixed
		 *
		 * @throws \Exception
		 */
		public function run($aCallback, array $aArgs = array()) {
			try {
				$this->begin();
				$result = Callback::invokeArgs($aCallback, $aArgs);
				$this->commit();
				return $result;
			} catch(\Exception $e) {
				$this->rollback();
				throw $e;
			}
		}

		/**
		 * řekne, zda je aktuální transakce otevřená (tzn. odstartovaná)
		 *
		 * @return bool
		 */
		public function isRunning() {
			return $this->open === true;
		}
	}
