<?php
	namespace Edde2\Validator;

	use Edde2\Object;

	class ValidatorService extends Object {
		/**
		 * @var ValidatorLoaderService
		 */
		private $validatorLoaderService;
		/**
		 * @var IValidatorList[]
		 */
		private $validator;
		/**
		 * @var bool
		 */
		private $loaded;

		public function __construct(ValidatorLoaderService $aValidatorLoaderService) {
			$this->validatorLoaderService = $aValidatorLoaderService;
		}

		public function register($aName, IValidatorList $aValidatorList) {
			$this->validator[$aName] = $aValidatorList;
			return $this;
		}

		protected function load() {
			if($this->loaded === true) {
				return $this;
			}
			foreach($this->validatorLoaderService->load() as $name => $sanitizer) {
				$this->register($name, $sanitizer);
			}
			$this->loaded = true;
			return $this;
		}

		public function has($aName) {
			return isset($this->validator[$aName]);
		}

		/**
		 * @param $aName
		 *
		 * @throws RuleNotFoundException
		 *
		 * @return IValidatorList
		 */
		public function get($aName) {
			/**
			 * lazy loading
			 */
			$this->load();
			if(!$this->has($aName)) {
				throw new RuleNotFoundException("Requested validator rule '$aName' not found.");
			}
			return $this->validator[$aName];
		}
	}
