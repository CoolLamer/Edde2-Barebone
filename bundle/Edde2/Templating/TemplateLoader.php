<?php
	namespace Edde2\Templating;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Caching\Cache;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Object;
	use Edde2\Utils\Strings;
	use Nette\Application\UI\Control;
	use Nette\Application\UI\ITemplate;
	use Nette\Application\UI\ITemplateFactory;
	use Nette\Bridges\ApplicationLatte\ILatteFactory;

	class TemplateLoader extends Object {
		/**
		 * @var ITemplateFactory
		 */
		private $templateFactory;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var ILatteFactory
		 */
		private $latteFactory;
		/**
		 * seznam všech známých šablon
		 *
		 * @var array
		 */
		private $template;

		public function __construct(ITemplateFactory $aTemplateFactory, CommonConfig $aCommonConfig, Cache $aCache, ILatteFactory $aFactory) {
			$this->templateFactory = $aTemplateFactory;
			$this->commonConfig = $aCommonConfig;
			$this->cache = $aCache;
			$this->latteFactory = $aFactory;
		}

		/**
		 * @param string $aDir
		 *
		 * @return \RecursiveIteratorIterator|\SplFileInfo[]
		 */
		protected function createIterator($aDir) {
			return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aDir, \RecursiveDirectoryIterator::SKIP_DOTS));
		}

		public function build() {
			if(isset($this->template) || ($this->template = $this->cache->load($cacheId = $this->cacheId('template'))) !== null) {
				return;
			}
			$this->template = array();
			$dirz = array(
				$this->commonConfig->getAppDir(),
				$this->commonConfig->getLibDir()
			);
			foreach($dirz as $dir) {
				foreach($this->createIterator($dir) as $path => $info) {
					if(!$info->isFile()) {
						continue;
					}
					if($info->getExtension() === 'latte') {
						$this->template[] = FileSystem::absolutize($path);
						continue;
					}
					if($info->getExtension() === 'phar') {
						foreach(new \RecursiveIteratorIterator(new \Phar($info->getPathname()), \RecursiveIteratorIterator::CHILD_FIRST) as $pharPath => $entry) {
							if($entry->isFile() && $entry->getExtension() === 'latte') {
								$this->template[] = $pharPath;
							}
						}
					}
				}
			}
			$this->cache->save($cacheId, $this->template);
		}

		/**
		 * @param string $aTemplate
		 *
		 * @return string
		 *
		 * @throws MultipleTemplateException
		 * @throws TemplateNotFoundException
		 */
		public function find($aTemplate) {
			$this->build();
			if(($list = $this->cache->load($cacheId = $this->cacheId('list'.$aTemplate))) === null) {
				$template = Strings::pregString($aTemplate);
				$list = preg_grep("~$template~", $this->template);
				$this->cache->save($cacheId, $list);
			}
			switch(count($list)) {
				case 0:
					throw new TemplateNotFoundException("Template '$aTemplate' not found.", $aTemplate);
					break;
				case 1:
					$list = reset($list);
					break;
				default:
					throw new MultipleTemplateException("Multiple template found by '$aTemplate'.", $aTemplate, $list);
					break;
			}
			return $list;
		}

		/**
		 * @param string $aTemplate
		 * @param Control $aControl
		 *
		 * @throws MultipleTemplateException
		 * @throws TemplateNotFoundException
		 *
		 * @return ITemplate
		 */
		public function create($aTemplate, Control $aControl) {
			$file = $this->find($aTemplate);
			$template = $this->templateFactory->createTemplate($aControl);
			$template->setFile($file);
			return $template;
		}

		/**
		 * @param string $aTemplate
		 *
		 * @return TemplateObject
		 * @throws MultipleTemplateException
		 * @throws TemplateNotFoundException
		 */
		public function template($aTemplate) {
			return new TemplateObject($this->latteFactory->create(), $this->find($aTemplate));
		}
	}
