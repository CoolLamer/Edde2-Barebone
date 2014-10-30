<?php
	namespace Edde2\Application\UI;

	/**
	 * Explicitně vynutí přihlášení uživatele; pokud není uživatel přihlášen, přesměruje jej. Také kontroluje přístupová práva
	 * uživatele.
	 */
	class BackendPresenter extends Presenter {
		protected function startup() {
			parent::startup();
			if(!$this->getUser()->isLoggedIn()) {
				$this->setView('../login');
			}
		}
	}
