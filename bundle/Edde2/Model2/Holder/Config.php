<?php
	namespace Edde2\Model2\Holder;

	use Edde2\Model2\Config\NeonLoaderService;
	use Edde2\Model2\Generator\Config as GeneratorConfig;
	use Edde2\Utils\ObjectEx;
	use Edde2\Utils\Strings;

	class Config extends ObjectEx {
		public function isDefault() {
			return $this->getOrDefault('default', false) === true;
		}

		public function getName() {
			return $this->get('name');
		}

		public function getClassName() {
			return sprintf('%s\Model\%sModelHolder', $this->getNamespace(), Strings::camelize($this->getName()));
		}

		public function getMask() {
			return $this->getOrDefault('mask', '*.model');
		}

		public function getNamespace() {
			return $this->getOrDefault('namespace', 'App\\Model');
		}

		/**
		 * @return string
		 */
		public function getConnection() {
			return $this->getOrDefault('connection', true);
		}

		public function getPath() {
			return $this->get('path');
		}

		/**
		 * @return GeneratorConfig
		 */
		public function getGenerator() {
			return $this->get('generator');
		}

		/**
		 * @return string|array
		 */
		public function getModelPath() {
			return $this->getGenerator()->getPath();
		}

		/**
		 * @return string
		 */
		public function getSourcePath() {
			return $this->getGenerator()->getPath().'/'.$this->getName();
		}

		/**
		 * @return string|array
		 */
		public function getImportPath() {
			return $this->getOrDefault('import-path', array());
		}

		/**
		 * @return string
		 */
		public function getLoaderService() {
			return $this->getOrDefault('loader-service', NeonLoaderService::getReflection()->getName());
		}
	}
