<?php
	namespace EddeModule\Component\DeploymenControl;

	use Edde2\Component\Control;
	use EddeModule\EddeService;
	use EddeModule\SandboxService\SandboxService;
	use EddeModule\SandboxService\UnknownImageFileException;
	use Nette\Application\UI\Form;
	use Nette\Reflection\ClassType;
	use Tracy\Debugger;

	class DeployControl extends Control {
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
			$container->addCheckbox('redirect', 'po publikaci přesměrovat');
			$container->addSubmit('upload', 'publikovat');
		}

		public function hookFormSubmit(Form $aForm) {
			try {
				Debugger::timer('deploy');
				$values = $aForm->getValues();
				$this->sandboxService->deploy($values->image->getTemporaryFile());
				if($values->redirect === true) {
					$this->getPresenter()->redirectUrl('/');
				}
				$this->message(sprintf('Aplikace byla úspěšně publikována. Čas odstávky %.2fs.', Debugger::timer('deploy')), 'success');
			} catch(UnknownImageFileException $e) {
				$this->message('Nahraný soubor neobsahuje obraz aplikace. Obraz aplikace musí být v ZIP souboru.', 'error');
			} catch(\Exception $e) {
				$this->message(sprintf('%s: %s', ClassType::from($e), $e->getMessage()), 'error');
			}
		}
	}
