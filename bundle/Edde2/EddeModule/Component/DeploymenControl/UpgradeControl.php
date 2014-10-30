<?php
	namespace EddeModule\Component\DeploymenControl;

	use Edde2\Component\Control;
	use EddeModule\SandboxService\SandboxService;
	use EddeModule\SandboxService\UnknownImageFileException;
	use Nette\Application\UI\Form;
	use Nette\Reflection\ClassType;
	use Tracy\Debugger;

	class UpgradeControl extends Control {
		/**
		 * @var SandboxService
		 */
		private $sandboxService;

		final public function injectSandboxService(SandboxService $aSandboxService) {
			$this->sandboxService = $aSandboxService;
		}

		protected function hookConstruct() {
			$container = $this->getFormContainer();
			$container->addUpload('image', 'obraz aplikace')->setRequired();
			$container->addSubmit('upload', 'upgradovat');
		}

		public function hookFormSubmit(Form $aForm) {
			try {
				Debugger::timer('upgrade');
				$this->sandboxService->upgrade($aForm->getValues()->image->getTemporaryFile());
				$this->message(sprintf('Aplikace byla úspěšně upgradována. Čas odstávky %.2fs.', Debugger::timer('upgrade')), 'success');
			} catch(UnknownImageFileException $e) {
				$this->message('Nahraný soubor neobsahuje obraz aplikace. Obraz aplikace musí být v ZIP souboru.', 'error');
			} catch(\Exception $e) {
				$this->message(sprintf('%s: %s', ClassType::from($e), $e->getMessage()), 'error');
			}
		}
	}
