<?php
	namespace Edde2\Model2;

	use Edde2\Bootstrap\CommonConfig;
	use Edde2\Bootstrap\Config;
	use Edde2\Model2\Generator\Config as GeneratorConfig;
	use Edde2\Model2\Holder\Config as HolderConfig;
	use Nette\DI\Container;

	class ModelServiceConfig extends Config {
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;

		public function __construct(Container $aContainer, CommonConfig $aCommonConfig) {
			$this->commonConfig = $aCommonConfig;
			/** ano, já vím, že za tohle by mi Java urazila ruce, ale potřebuji prve nastavit závislosti.... */
			parent::__construct($aContainer);
		}

		public function build(array $aProperties = null) {
			if(!isset($aProperties['model'])) {
				throw new MissingModelConfigException('Requested ModelConfig without model config section in parameters. Please configure model service.');
			}
			if(!is_array(reset($aProperties['model']))) {
				$model = $aProperties['model'];
				$model['default'] = true;
				$aProperties['model'] = array('app' => $model);
			}
			$aProperties['model']['edde'] = array(
				'mask' => '*.model',
				'path' => $this->commonConfig->getLibDir(),
				'namespace' => 'Edde2',
				'import-path' => $this->commonConfig->getLibDir(),
				'generator' => array(
					'path' => sprintf('%s/model', $this->commonConfig->getTempDir())
				),
			);
			foreach($aProperties['model'] as $name => $config) {
				$this->property($name, $config = new HolderConfig($config));
				$config->property('name', $name);
				if($config->has('generator')) {
					$config->set('generator', new GeneratorConfig($config->get('generator')));
				}
				if($config->isDefault()) {
					$this->property('@default', $config);
				}
			}
			return $this;
		}

		/**
		 * @return HolderConfig
		 */
		public function getDefault() {
			return $this->get('@default');
		}

		/**
		 * @return HolderConfig
		 */
		public function getEdde() {
			return $this->get('edde');
		}

		/**
		 * @param string $aName
		 *
		 * @throws UnknownHolderException
		 *
		 * @return HolderConfig
		 */
		public function getConfig($aName) {
			if(!$this->has($aName)) {
				throw new UnknownHolderException(sprintf('Unknown holder config [%s].', $aName));
			}
			return $this->get($aName);
		}
	}
