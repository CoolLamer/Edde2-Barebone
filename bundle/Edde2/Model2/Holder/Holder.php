<?php
	namespace Edde2\Model2\Holder;

	use Edde2\Database\Connection;
	use Edde2\Model2\Config\Config as ModelConfig;
	use Edde2\Model2\Config\ILoaderService;
	use Edde2\Model2\Model;
	use Edde2\Model2\Query\Query;
	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Sanitizer\RuleNotFoundException as SanitizerRuleNotFoundException;
	use Edde2\Sanitizer\SanitizerService;
	use Edde2\Validator\RuleNotFoundException as ValidatorRuleNotFoundException;
	use Edde2\Validator\ValidatorService;

	/**
	 * Třída napojující aplikaci na konkrétní kontext modelů (konfiguraci modelů).
	 */
	abstract class Holder extends Object {
		/**
		 * @var Config
		 */
		private $config;
		/**
		 * @var ILoaderService
		 */
		private $loaderService;
		/**
		 * @var Connection
		 */
		private $connection;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var SanitizerService
		 */
		private $sanitizerService;
		/**
		 * @var ValidatorService
		 */
		private $validatorService;

		public function __construct(Config $aConfig, ILoaderService $aLoaderService, Connection $aConnection, IClassLoader $aClassLoader, SanitizerService $aSanitizerService, ValidatorService $aValidatorService) {
			$this->config = $aConfig;
			$this->loaderService = $aLoaderService;
			$this->connection = $aConnection;
			$this->classLoader = $aClassLoader;
			$this->sanitizerService = $aSanitizerService;
			$this->validatorService = $aValidatorService;
		}

		/**
		 * @return Connection
		 */
		public function getConnection() {
			return $this->connection;
		}

		/**
		 * @return ILoaderService
		 */
		public function getLoaderService() {
			return $this->loaderService;
		}

		/**
		 * @param string $aName
		 *
		 * @return ModelConfig
		 */
		public function config($aName) {
			return $this->loaderService->getModelConfig($aName);
		}

		/**
		 * @param string $aName
		 *
		 * @return Model
		 */
		public function model($aName) {
			$config = $this->loaderService->getModelConfig($aName);
			/** @var $model Model */
			$model = $this->classLoader->create(sprintf('\\%sModel', $config->getName()), array(
				$this,
				$config
			));
			try {
				$model->sanitizer($this->sanitizerService->get($config->getName()));
			} catch(SanitizerRuleNotFoundException $e) {
			}
			try {
				$model->validator($this->validatorService->get($config->getName()));
			} catch(ValidatorRuleNotFoundException $e) {
			}
			$model->property('id');
			$model->properties(array_keys($config->getPhysicalPropertyList()));
			return $model;
		}

		/**
		 * @param string|Model $aModel
		 *
		 * @return Query
		 */
		public function query($aModel) {
			return $this->classLoader->create(sprintf('\\%sQuery', $aModel instanceof Model ? $aModel->getModelName() : $aModel), array(
				$this->connection->createQuery(),
				$aModel instanceof Model ? $aModel : $this->config($aModel),
				$this
			));
		}

		public function save(Model $aModel) {
			/**
			 * prve je potřeba uložit všechny závislosti (napojené modely)
			 */
			foreach($aModel->getBindList() as $property => $bind) {
				$this->save($bind);
				$aModel->set($aModel->getModelProperty($property)->getReference()->getName(), $bind->getId());
			}
			$table = $this->connection->context()->table($aModel->config()->getSourceName());
			$changes = $aModel->changes();
			unset($changes['id']);
			if($aModel->isRemove()) {
				$table->wherePrimary($aModel->getId())->delete();
			} else if($aModel->changed() && $aModel->hasId()) {
				$table->wherePrimary($aModel->getId())->update($changes);
			} else if($aModel->changed()) {
				$aModel->setId($table->insert($changes)->getPrimary());
			}
			/**
			 * change zahodí veškeré připojené seznamy, tudíž je nutné si jej vzít před change; za cyklem to nejde, protože jinak se
			 * uložení rekurzivně zamotá
			 */
			$bindList = $aModel->getList();
			$aModel->change();
			/** @var $bindList Model[][] */
			foreach($bindList as $list) {
				foreach($list as $model) {
					$model->save();
				}
			}
			return $aModel;
		}
	}
