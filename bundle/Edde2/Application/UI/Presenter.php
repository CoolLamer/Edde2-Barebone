<?php
	namespace Edde2\Application\UI;

	use Edde2\Application\Responses\TextResponse;
	use Edde2\Component\Control;
	use Edde2\Component\IComponentService;
	use Edde2\Security\User;
	use Nette\Application\Responses\JsonResponse;
	use Nette\Application\UI\Presenter as NettePresenter;
	use Nette\ComponentModel\IComponent;
	use Nette\Neon\Neon;

	class Presenter extends NettePresenter {
		/**
		 * @var IComponentService
		 */
		private $componentService;

		final public function injectComponentService(IComponentService $aComponentService) {
			$this->componentService = $aComponentService;
		}

		/**
		 * @param string $aName
		 *
		 * @return Control|IComponent
		 */
		protected function createComponent($aName) {
			return $this->componentService->create($aName, $this, $aName);
		}

		protected function startup() {
			parent::startup();
			/**
			 * nutné, jinak se neustále redirectuje na jiné url, než jaké nacheatuje router
			 */
			$this->autoCanonicalize = false;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			return parent::getUser();
		}

		public function sendText($aText, $aContentType = 'text/plain') {
			$this->sendResponse(new TextResponse($aText, $aContentType));
		}

		public function sendNeon($aValue, $aContentType = 'text/plain') {
			$this->sendResponse(new TextResponse(Neon::encode($aValue), $aContentType));
		}

		public function sendJson($aValue) {
			$this->sendResponse(new JsonResponse($aValue));
		}
	}
