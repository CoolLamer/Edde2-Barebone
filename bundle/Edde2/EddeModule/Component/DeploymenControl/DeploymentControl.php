<?php
	namespace IndexModule\Component\DeploymentControl;

	use Edde2\Component\Control;
	use Edde2\EddeModule\Component\DeploymenControl\CacheControl;
	use Edde2\EddeModule\Component\DeploymenControl\MaintenanceControl;
	use Edde2\EddeModule\Component\DeploymenControl\RebuildControl;
	use EddeModule\Component\DeploymenControl\DeployControl;
	use EddeModule\Component\DeploymenControl\UpgradeControl;

	class DeploymentControl extends Control {
		protected function hookConstruct() {
			$this->register('deploy', DeployControl::getReflection()->getName());
			$this->register('upgrade', UpgradeControl::getReflection()->getName());
			$this->register('cache', CacheControl::getReflection()->getName());
			$this->register('maintenance', MaintenanceControl::getReflection()->getName());
			$this->register('rebuild', RebuildControl::getReflection()->getName());
		}
	}
