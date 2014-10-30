<?php
	namespace Edde2\EddeModule\Component\DeploymenControl;

	use Edde2\Component\Control;
	use EddeModule\SandboxService\SandboxService;

	class MaintenanceControl extends Control {
		/**
		 * @var SandboxService
		 */
		private $sandboxService;

		final public function injectSandboxService(SandboxService $aSandboxService) {
			$this->sandboxService = $aSandboxService;
		}

		public function handleOffline() {
			$this->sandboxService->enableMaintenanceMode();
			$this->message('Aplikace byla odstavena - nyní je offline.', 'warning');
		}

		public function handleOnline() {
			$this->sandboxService->disableMaintenanceMode();
			$this->message('Aplikace byla opět zapnuta, nyní je online.', 'success');
		}
	}
