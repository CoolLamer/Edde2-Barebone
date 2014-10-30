<?php
	namespace Edde2\Component;

	use Edde2\Templating\TemplateLoader;
	use Edde2\Templating\TemplateNotFoundException;
	use Edde2\Utils\Strings;
	use EddeModule\Component\FlashMessageControl;
	use Nette\Application\UI\Control as NetteControl;
	use Nette\Application\UI\Form;
	use Nette\Application\UI\ITemplate;
	use Nette\ComponentModel\IComponent;
	use Nette\ComponentModel\IContainer;
	use Nette\Forms\Container;
	use Nette\Http\Request;

	/**
	 * Základní komponenta - obsahuje nativní podporu pro ajax (tohoto zejména využívá obalová komponenta pro formuláře).
	 */
	abstract class Control extends NetteControl {
		/**
		 * @var TemplateLoader
		 */
		private $temploadLoader;
		/**
		 * @var IComponentService
		 *
		 * služba pro vytváření komponent musí být přítomná i zde pro generování subkomponent
		 */
		private $componentService;
		/**
		 * @var Request
		 */
		private $httpReqeust;
		/**
		 * @var array
		 *
		 * registr jméno-třídy komponenty; pokud není null, komponenta jej využije pro vytváření subkomponent; užitečné, pokud není vhodné umožnit vytvořit libovolnou komponentu
		 */
		private $register;
		/**
		 * @var Form
		 */
		private $form;

		final public function __construct(IContainer $aParent, $aName, TemplateLoader $aTemplateLoader, IComponentService $aComponentService, Request $aHttpRequest) {
			parent::__construct($aParent, $aName);
			$this->temploadLoader = $aTemplateLoader;
			$this->componentService = $aComponentService;
			$this->httpReqeust = $aHttpRequest;
		}

		/**
		 * háček, který je volaný automaticky v konstruktoru; komponenta si může např. zaregistrovat subkomponenty
		 */
		protected function hookConstruct() {
		}

		/**
		 * zaregistruje subkomponentu; ta se pak vytváří dle zadaného jména
		 *
		 * @param string $aName
		 * @param string $aClassName
		 *
		 * @return $this
		 */
		protected function register($aName, $aClassName) {
			$this->register[$aName] = $aClassName;
			return $this;
		}

		/**
		 * @param string $aName
		 *
		 * @return Control
		 *
		 * @throws UnknownComponentException
		 */
		public function createComponent($aName) {
			if(isset($this->register[$aName])) {
				return $this->createComponentEx($this->register[$aName], $aName);
			}
			return $this->componentService->create($aName, $this, $aName);
		}

		/**
		 * @param string $aClass
		 * @param string $aName
		 *
		 * @return IComponent
		 */
		public function createComponentEx($aClass, $aName) {
			return $this->componentService->create($aClass, $this, $aName);
		}

		/**
		 * @param string $aName
		 *
		 * @return Container
		 */
		protected function getFormContainer($aName = null) {
			if($this->form === null) {
				$this->form = new Form($this, 'form');
				$this->form->onSubmit[] = array(
					$this,
					'hookFormSubmit'
				);
			}
			if($aName === null) {
				return $this->form;
			}
			return $this->form->addContainer($aName);
		}

		public function hookBeforeRender($aTemplate) {
		}

		protected function getTemplateFileName() {
			return $this->temploadLoader->find(Strings::recamel($this->reflection->getShortName()).'.latte');
		}

		/**
		 * @param string $aName
		 *
		 * @return ITemplate
		 */
		protected function loadTemplate($aName) {
			return $this->temploadLoader->create($aName, $this);
		}

		public function render() {
			try {
				$template = $this->template;
				$template->setFile($this->getTemplateFileName());
				$template->form = $this->form;
				$this->hookBeforeRender($template);
				$template->render();
			} catch(TemplateNotFoundException $e) {
			}
		}

		public function response(array $aResponse) {
			$this->getPresenter()->sendJson($aResponse);
		}

		protected function getPost($aKey = null, $aDefault = null) {
			return $this->httpReqeust->getPost($aKey, $aDefault);
		}

		public function hookFormSubmit(Form $aForm) {
		}

		public function message($aMessage, $aType = 'info') {
			/** @var $flashMessageControl FlashMessageControl */
			$flashMessageControl = $this->getComponent('FlashMessageControl');
			$flash = $flashMessageControl->flashMessage($aMessage, $aType);
			$flashMessageControl->redrawControl();
			return $flash;
		}
	}
