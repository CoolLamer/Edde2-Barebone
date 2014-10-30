<?php
	namespace Edde2\Database;

	use Edde2\Object;
	use Nette\Utils\Callback;

	/**
	 * Slouží pro nasazení do míst, která počítají s transakcí, ale kde je potřeba transakci vypnout.
	 */
	class DummyTransaction extends Object implements ITransaction {
		public function begin() {
		}

		public function commit() {
		}

		public function rollback() {
		}

		public function run($aCallback, array $aArgs = array()) {
			return Callback::invokeArgs($aCallback, $aArgs);
		}

		public function isRunning() {
			throw new TransactionException("Don't ask dummy transaction for DummyTransaction::isRunning()! If you use this method, something is wrong. And god will kill one cute kitten. Because of you. Remember it!");
		}
	}
