<?php
	namespace Edde2\Model2\Generator;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\FileSystem\FileSystem;
	use Edde2\Model2\Config\Config as ModelConfig;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Model2\Holder\Config as HolderConfig;
	use Edde2\Object;
	use Edde2\Reflection\EddeLoader;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Templating\TemplateLoader;
	use Edde2\Utils\Strings;

	class SourceGenerator extends Object {
		/**
		 * @var ILoaderService|ModelConfig[]
		 */
		private $loaderService;
		/**
		 * @var HolderConfig
		 */
		private $holderConfig;
		/**
		 * @var TemplateLoader
		 */
		private $templateLoader;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var EddeLoader
		 */
		private $eddeLoader;
		/**
		 * znovu sestavit index tříd pro okamžité použití?
		 *
		 * @var bool
		 */
		private $reload = false;

		public function __construct(ILoaderService $aLoaderService, HolderConfig $aHolderConfig, TemplateLoader $aTemplateLoader, CommonConfig $aCommonConfig, IClassLoader $aClassLoader, EddeLoader $aLoader) {
			$this->loaderService = $aLoaderService;
			$this->holderConfig = $aHolderConfig;
			$this->templateLoader = $aTemplateLoader;
			$this->commonConfig = $aCommonConfig;
			$this->classLoader = $aClassLoader;
			$this->eddeLoader = $aLoader;
		}

		public function getPhpType($aType) {
			switch($aType) {
				case 'datetime':
					return '\\Nette\\Utils\\DateTime';
				case 'text':
					return 'string';
			}
			return $aType;
		}

		public function isBool($aType) {
			return in_array($aType, array(
				'b',
				'bool'
			));
		}

		public function isDateTime($aType) {
			return in_array($aType, array('datetime'));
		}

		/**
		 * sestavit index tříd znovu po vygenerování zdrojových souborů? (aplikace může okamžitě využívat nové třídy)
		 *
		 * @return $this
		 */
		public function reload() {
			$this->reload = true;
			return $this;
		}

		public function generate() {
			$namespace = $this->holderConfig->getNamespace();
			$path = $this->holderConfig->getSourcePath();
			$holderClass = sprintf('%sModelHolder', Strings::camelize($this->holderConfig->getName()));
			FileSystem::recreate($path);
			FileSystem::createDir("$path/Model");
			FileSystem::createDir("$path/Query");
			$this->eddeLoader->reindex();
			$this->classLoader->rebuild($this->eddeLoader);
			$templateArgz = array(
				'namespace' => $namespace,
				'holderClass' => $holderClass,
				'holderName' => $this->holderConfig->getName(),
				'generator' => $this,
				'loader' => $this->loaderService,
			);
			$holderSource = $this->templateLoader->template('source-holder.latte');
			$holderSource->propertyAll($templateArgz);
			$holderSource->setPrefix("<?php\n");
			$holderSource->save(sprintf('%s/%s.php', $path, $holderClass));
			$modelSource = $this->templateLoader->template('source-model.latte');
			$modelSource->propertyAll($templateArgz);
			$modelSource->setPrefix("<?php\n");
			$querySource = $this->templateLoader->template('source-query.latte');
			$querySource->propertyAll($templateArgz);
			$querySource->setPrefix("<?php\n");
			foreach($this->loaderService as $config) {
				if($config->isVirtual()) {
					continue;
				}
				$modelSource->property('config', $config);
				$querySource->property('config', $config);
				$modelSource->save(sprintf('%s/Model/%sModel.php', $path, $config->getName()));
				$querySource->save(sprintf('%s/Query/%sQuery.php', $path, $config->getName()));
			}
			if($this->reload === true) {
				$this->eddeLoader->addDirectory($path)->reindex();
				$this->classLoader->rebuild($this->eddeLoader);
			}
		}
	}
