<?php
	namespace Edde2\Templating;

	use Edde2\Utils\ObjectEx;
	use Latte\Engine;

	class TemplateObject extends ObjectEx {
		/**
		 * @var Engine
		 */
		private $latte;
		private $templateFile;
		private $prefix;

		public function __construct(Engine $aLatte, $aTemplateFile) {
			$this->latte = $aLatte;
			$this->templateFile = $aTemplateFile;
		}

		public function setPrefix($aPrefix) {
			$this->prefix = $aPrefix;
			return $this;
		}

		public function string() {
			return $this->prefix.$this->latte->renderToString($this->templateFile, $this->current());
		}

		public function save($aFile) {
			file_put_contents($aFile, $this->string());
			return $this;
		}

		public function render() {
			echo $this->string();
		}

		public function __toString() {
			return $this->string();
		}
	}
