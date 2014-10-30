<?php
	namespace Edde2\Component;

	use Nette\ComponentModel\IContainer;

	class ComponentNotFoundException extends ComponentException {
		/**
		 * @var IComponentService
		 */
		private $componentService;
		/**
		 * @var string
		 */
		private $component;
		/**
		 * @var IContainer
		 */
		private $parent;
		/**
		 * @var string
		 */
		private $name;

		/**
		 * @param string $aMessage
		 * @param IComponentService $aComponentService
		 * @param string $aComponent
		 * @param IContainer $aParent
		 * @param string $aName
		 */
		public function __construct($aMessage, IComponentService $aComponentService, $aComponent, IContainer $aParent, $aName) {
			parent::__construct($aMessage);
			$this->componentService = $aComponentService;
			$this->component = $aComponent;
			$this->parent = $aParent;
			$this->name = $aName;
		}

		/**
		 * @return IComponentService
		 */
		public function getComponentService() {
			return $this->componentService;
		}

		/**
		 * @return string
		 */
		public function getComponent() {
			return $this->component;
		}

		/**
		 * @return IContainer
		 */
		public function getParent() {
			return $this->parent;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
	}
