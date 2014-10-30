<?php
	namespace Edde2\Security;

	/**
	 * Vyhazuje se, pokud se uživatel snaží přistoupit ke zdroji, ke kterému nemá oprávnění.
	 */
	class AclException extends SecurityException {
		/**
		 * @var User
		 */
		private $user;
		/**
		 * @var string
		 */
		private $resource;
		/**
		 * @var string
		 */
		private $privilege;

		public function __construct($aMessage, User $aUser, $aResource, $aPrivilege = null) {
			parent::__construct($aMessage);
			$this->resource = $aResource;
			$this->privilege = $aPrivilege;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			return $this->user;
		}

		/**
		 * @return string
		 */
		public function getPrivilege() {
			return $this->privilege;
		}

		/**
		 * @return string
		 */
		public function getResource() {
			return $this->resource;
		}

		/**
		 * @return string
		 */
		public function getResourceString() {
			return $this->user->getResourceString($this->resource, $this->privilege);
		}
	}
