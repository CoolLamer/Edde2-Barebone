<?php
	namespace Edde2\Sanitizer;

	use Edde2\Object;

	/**
	 * Sanitizační služba - jejím smyslem je umožnit vstupně výstupní převod dat; např. směrem k uživateli jde datum v lokalizované verzi pro daného
	 * uživatele, ale směrem do aplikace jde datum standardizované.
	 */
	class SanitizerService extends Object {
		/**
		 * @var SanitizerLoaderService
		 */
		private $sanitizerLoaderService;
		/**
		 * @var ISanitizer
		 */
		private $sanitizer;
		/**
		 * @var bool
		 */
		private $loaded;

		public function __construct(SanitizerLoaderService $aSanitizerLoaderService) {
			$this->sanitizerLoaderService = $aSanitizerLoaderService;
		}

		public function register($aName, ISanitizer $aSanitizerRule) {
			$this->sanitizer[$aName] = $aSanitizerRule;
			return $this;
		}

		protected function load() {
			if($this->loaded === true) {
				return $this;
			}
			foreach($this->sanitizerLoaderService->load() as $name => $sanitizer) {
				$this->register($name, $sanitizer);
			}
			$this->loaded = true;
			return $this;
		}

		public function has($aName) {
			return isset($this->sanitizer[$aName]);
		}

		/**
		 * @param string $aName
		 *
		 * @return ISanitizer
		 *
		 * @throws RuleNotFoundException
		 */
		public function get($aName) {
			/**
			 * lazy loading
			 */
			$this->load();
			if(!$this->has($aName)) {
				throw new RuleNotFoundException("Requested sanitizer rule '$aName' not found.");
			}
			return $this->sanitizer[$aName];
		}
	}
