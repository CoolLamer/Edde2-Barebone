<?php
	namespace Edde2\Templating;

	class MultipleTemplateException extends CreateException {
		/**
		 * @var array
		 */
		private $template;

		public function __construct($aMessage, $aName, array $aTemplates) {
			parent::__construct($aMessage, $aName);
			$this->template = $aTemplates;
		}

		/**
		 * @return array
		 */
		public function getTemplate() {
			return $this->template;
		}
	}
