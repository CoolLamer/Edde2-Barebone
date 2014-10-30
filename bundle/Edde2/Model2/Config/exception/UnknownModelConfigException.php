<?php
	namespace Edde2\Model2\Config;

	class UnknownModelConfigException extends ConfigException {
		/**
		 * @var string
		 */
		private $query;

		/**
		 * @param string $aMessage
		 * @param string $aQuery
		 */
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
