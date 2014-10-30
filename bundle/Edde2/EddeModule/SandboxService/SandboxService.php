<?php
	namespace EddeModule\SandboxService;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\EddeModule\SandboxService\DeployException;
	use Edde2\FileSystem\File;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Object;
	use Edde2\Templating\TemplateLoader;
	use Edde2\Utils\Strings;
	use Nette\Http\Request;
	use Nette\Neon\Neon;

	/**
	 * Serverová část pro deploy/upgrade - stará se o správu a volání akcí nad aplikacemi; klientská část, která má přijímat a zpracovávat konkrétní požadavky je
	 * EddeService.
	 */
	class SandboxService extends Object {
		/**
		 * @var TemplateLoader
		 */
		private $templateLoader;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var Request
		 */
		private $httpRequest;

		public function __construct(TemplateLoader $aTemplateLoader, CommonConfig $aCommonConfig, Request $aHttpRequest) {
			$this->templateLoader = $aTemplateLoader;
			$this->commonConfig = $aCommonConfig;
			$this->httpRequest = $aHttpRequest;
		}

		protected function getAppPath() {
			return sprintf('%s/.app', $this->commonConfig->getTempDir());
		}

		public function deploy($aApplicationImageFile) {
			if(File::mime($aApplicationImageFile) !== 'application/zip') {
				throw new UnknownImageFileException(sprintf('Unknown application image file [%s]. Please provide ZIP archive.', $aApplicationImageFile));
			}
			$this->enableMaintenanceMode();
			File::unzip($aApplicationImageFile, $this->getAppPath());
			$this->createIndexPhpFile($appIndexPhp = sprintf('%s.php', Strings::hash(date('Y-m-d-Hi-s'))));

			$config = $this->call($appIndexPhp, 'Edde:App:config');
			$this->call($appIndexPhp, 'Edde:App:deploy');
			FileSystem::clean($this->commonConfig->getWebDir());
			FileSystem::merge($config['wwwDir'], $this->commonConfig->getWebDir());
			FileSystem::copy(sprintf('%s/.edde', $this->commonConfig->getAppDir()), sprintf('%s/.edde', $this->commonConfig->getWebDir()));
			/** díky uložení do tmp file bude tento soubor publikován funkcí disableMainentanceMode */
			$this->createIndexPhpFile();
			$this->disableMaintenanceMode();
		}

		public function upgrade($aApplicationImageFile) {
			if(File::mime($aApplicationImageFile) !== 'application/zip') {
				throw new UnknownImageFileException(sprintf('Unknown application image file [%s]. Please provide ZIP archive.', $aApplicationImageFile));
			}
		}

		protected function createIndexPhpFile($aIndexPhpFile = 'index.php.tmp') {
			$indexTemplate = $this->templateLoader->template('index.php.latte');
			$indexTemplate->setPrefix('<?php ');
			$indexTemplate->property('bootstrap', sprintf('%s/app/bootstrap.php', $this->getAppPath()));
			$indexTemplate->save(sprintf('%s/%s', $this->commonConfig->getWebDir(), $aIndexPhpFile));
		}

		protected function getIndexPhpFile() {
			return sprintf('%s/index.php', $this->commonConfig->getWebDir());
		}

		protected function getIndexPhpTmpFile() {
			return sprintf('%s/index.php.tmp', $this->commonConfig->getWebDir());
		}

		public function enableMaintenanceMode() {
			if(!file_exists($indexPhpFile = $this->getIndexPhpFile())) {
				return;
			}
			FileSystem::rename($indexPhpFile, $this->getIndexPhpTmpFile());
			$this->templateLoader->template('templates/index.html.latte')->save($indexPhpFile);
		}

		public function disableMaintenanceMode() {
			FileSystem::rename($this->getIndexPhpTmpFile(), $this->getIndexPhpFile());
		}

		protected function call($aIndexPhpFile, $aAction, array $aArgz = array()) {
			$action = explode(':', $aAction);
			foreach($action as &$part) {
				$part = Strings::recamel($part);
			}
			$url = sprintf('%s/%s/%s/%s', $this->httpRequest->getUrl()->getHostUrl(), $aIndexPhpFile, implode('/', $action), !empty($aArgz) ? '?'.http_build_query($aArgz) : null);
			if(!($result = @file_get_contents($url))) {
				throw new DeployException(sprintf('Cannot call [%s]. Request failed.', $url));
			}
			return Neon::decode($result);
		}
	}
