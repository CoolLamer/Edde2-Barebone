<?php
	namespace Edde2\EddeModule\Component\DeploymenControl;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Component\Control;
	use EddeModule\EddeService\EddeService;
	use Nette\Reflection\ClassType;
	use Tracy\Debugger;

	class RebuildControl extends Control {
		/**
		 * @var EddeService
		 */
		private $eddeService;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;

		final public function injectCommonConfig(CommonConfig $aCommonConfig) {
			$this->commonConfig = $aCommonConfig;
		}

		final public function injectEddeService(EddeService $aEddeService) {
			$this->eddeService = $aEddeService;
		}

		protected function hookConstruct() {
			if(!$this->commonConfig->isDebugMode()) {
				$this->message('POZOR! Aplikace neběží ve vývojařském režimu; použitím riskujete ztrátu produkčních dat!', 'error');
			}
			$this->template->debugMode = $this->commonConfig->isDebugMode();
		}

		public function handleSources() {
			Debugger::timer('rebuild');
			$this->eddeService->rebuildSources();
			$this->message(sprintf('Zdrojové soubory byly přegenerovány [%.2fs].', Debugger::timer('rebuild')), 'success');
		}

		public function handleDatabase() {
			try {
				Debugger::timer('rebuild');
				$this->eddeService->rebuildDatabase();
				$this->message(sprintf('Databáze byla přegenerována [%.2fs].', Debugger::timer('rebuild')), 'success');
			} catch(\Exception $e) {
				$this->message(sprintf('%s: %s.', ClassType::from($e)->getName(), $e->getMessage()), 'error');
			}
		}

		public function handleRebuild() {
			try {
				Debugger::timer('rebuild');
				$this->eddeService->rebuild();
				$this->message(sprintf('Aplikace byla přegenerována [%.2fs].', Debugger::timer('rebuild')), 'success');
			} catch(\Exception $e) {
				$this->message(sprintf('%s: %s.', ClassType::from($e)->getName(), $e->getMessage()), 'error');
			}
		}
	}
