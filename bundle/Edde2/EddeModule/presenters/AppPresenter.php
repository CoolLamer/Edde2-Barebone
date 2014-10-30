<?php
	namespace EddeModule;

	use Edde2\Application\UI\Presenter;
	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Database\ConnectionService;
	use Edde2\FileSystem\NeonFile;
	use Edde2\Model2\ModelService;
	use EddeModule\EddeService\EddeService;
	use Nette\Application\AbortException;
	use Nette\PhpGenerator\ClassType;
	use Nette\Utils\Callback;
	use Tracy\Debugger;

	class AppPresenter extends Presenter {
		/**
		 * @var EddeService
		 */
		private $eddeService;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var ModelService
		 */
		private $modelService;
		/**
		 * @var ConnectionService
		 */
		private $connectionService;

		final public function injectEddeService(EddeService $aEddeService) {
			$this->eddeService = $aEddeService;
		}

		final public function injectCommonConfig(CommonConfig $aCommonConfig) {
			$this->commonConfig = $aCommonConfig;
		}

		final public function injectModelService(ModelService $aModelService) {
			$this->modelService = $aModelService;
		}

		final public function injectConnectionService(ConnectionService $aConnectionService) {
			$this->connectionService = $aConnectionService;
		}

		public function actionConfig() {
			$this->sendNeon($this->commonConfig->getAll());
		}

		protected function performAction($aCallback) {
			try {
				Callback::invoke($aCallback);
				$this->sendNeon(array('info' => 'done'));
			} catch(AbortException $e) {
				throw $e;
			} catch(\Exception $e) {
				Debugger::log($e);
				$this->sendNeon(array(
					'error' => array(
						'class' => ClassType::from($e)->getName(),
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
					)
				));
			}
		}

		public function actionDeploy() {
			$this->performAction(array(
				$this->eddeService,
				'deploy'
			));
		}

		public function actionUpgrade($config) {
			$configFile = new NeonFile($config);
			$config = $configFile->getContents();
//			$this->eddeService->upgrade($config);
			$this->sendNeon('ok');
		}
	}
