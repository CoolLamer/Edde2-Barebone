<?php
	namespace Edde2\Application\UI;

	use Edde2\Bootstrap\CommonConfig;
	use Nette\Application\Responses\TextResponse;

	/**
	 * Tento presenter umožní spustit pouze z CLI.
	 */
	class CliPresenter extends Presenter {
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;

		final public function injectCommonConfig(CommonConfig $aCommonConfig) {
			$this->commonConfig = $aCommonConfig;
		}

		protected function startup() {
			parent::startup();
			if(!$this->commonConfig->isConsoleMode()) {
				$this->sendResponse(new TextResponse('cli-mode only'));
				$this->terminate();
			}
		}
	}
