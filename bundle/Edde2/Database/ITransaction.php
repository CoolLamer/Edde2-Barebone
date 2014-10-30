<?php
	namespace Edde2\Database;

	interface ITransaction {
		public function begin();

		public function commit();

		public function rollback();

		/**
		 * @param callback $aCallback
		 * @param array $aArgs
		 *
		 * @return mixed
		 */
		public function run($aCallback, array $aArgs = array());

		/**
		 * @return bool
		 */
		public function isRunning();
	}
