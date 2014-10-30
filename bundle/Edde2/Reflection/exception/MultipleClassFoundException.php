<?php
	namespace Edde2\Reflection;

	class MultipleClassFoundException extends FindException {
		/**
		 * @var array
		 */
		private $classes;

		public function __construct($aMessage, $aQuery, array $aClasses) {
			parent::__construct($aMessage, $aQuery);
			$this->classes = $aClasses;
		}

		/**
		 * @return array
		 */
		public function getClasses() {
			return $this->classes;
		}
	}

