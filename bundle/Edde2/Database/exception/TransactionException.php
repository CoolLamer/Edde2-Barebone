<?php
	namespace Edde2\Database;

	class TransactionException extends DatabaseException {
		/**
		 * @var ITransaction
		 */
		private $transaction;

		/**
		 * @param string $aMessage
		 * @param ITransaction $aTransaction
		 */
		public function __construct($aMessage, ITransaction $aTransaction = null) {
			parent::__construct($aMessage);
			$this->transaction = $aTransaction;
		}

		/**
		 * @return ITransaction
		 */
		public function getTransaction() {
			return $this->transaction;
		}
	}
