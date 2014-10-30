<?php
	namespace Edde2\EddeModule\Component\DeploymenControl;

	use Edde2\Component\Control;
	use EddeModule\EddeService\EddeService;
	use EddeModule\SandboxService\SandboxService;

	class CacheControl extends Control {
		/**
		 * @var EddeService
		 */
		private $eddeService;

		final public function injectEddeService(EddeService $aEddeService) {
			$this->eddeService = $aEddeService;
		}

		public function handleClean() {
			$this->eddeService->cleanCache();
			$this->message('Cache byla vyčištěna.', 'warning');
		}
	}
