<?php
	namespace Edde2\Reflection;

	/**
	 * Vyhazuje se při vyhledávání třídy, kdy třída není nalezena.
	 */
	class FindException extends ReflectionException {
		/**
		 * @var string
		 */
		private $query;

		public function __construct($aMessage, $aQuery) {
			parent::__construct($aMessage);
			$this->query = $aQuery;
		}

		/**
		 * @return string
		 */
		public function getQuery() {
			return $this->query;
		}
	}
