<?php
	namespace Edde2\Reflection;

	use Edde2\Object;

	class MetaInfo extends Object {
		private $name;
		private $type;
		private $abstract;
		private $file;
		private $interfaceList = array();
		private $implementorList = array();

		public function __construct($aName, $aFile, $aType, $aAbstract) {
			$this->name = $aName;
			$this->file = $aFile;
			$this->type = $aType;
			$this->abstract = $aAbstract;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * @return int
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getTypeString() {
			return token_name($this->getType());
		}

		/**
		 * @return bool
		 */
		public function isAbstract() {
			return $this->abstract;
		}

		public function getFile() {
			return $this->file;
		}

		public function setInterfaceList(array $aInterfaceList) {
			$this->interfaceList = $aInterfaceList;
			return $this;
		}

		/**
		 * @return string[]
		 */
		public function getInterfaceList() {
			return $this->interfaceList;
		}

		public function addImplementor($aName) {
			$this->implementorList[$aName] = $aName;
			return $this;
		}

		public function getImplementor() {
			return reset($this->implementorList);
		}

		public function isImplementor($aInterface) {
			return in_array($aInterface, $this->interfaceList);
		}

		/**
		 * @return string[]
		 */
		public function getImplementorList() {
			return $this->implementorList;
		}
	}
