<?php
	namespace Edde2\Sanitizer;

	use Edde2\Object;

	/**
	 * Pravidlo sanitizace - obsahuje sadu sanitizátorů, která je aplikovaná na daná vstupní data.
	 */
	class SanitizerRule extends Object implements ISanitizerRule {
		/**
		 * @var IFilter[]
		 */
		private $filter;

		/**
		 * zaregistruje do této sady pravidel další filtr; na pořadí registrace záleží
		 *
		 * @param IFilter $aFilter
		 *
		 * @return $this
		 */
		public function register(IFilter $aFilter) {
			$this->filter[] = $aFilter;
			return $this;
		}

		/**
		 * na vstupu je hodnota směrem do aplikace (tzn. zpravidla sanitizace před validací a uložením do databáze)
		 *
		 * @param mixed $aInput
		 *
		 * @return mixed
		 *
		 * @throws NoSanitizerException
		 */
		public function input($aInput) {
			if($this->filter === null) {
				throw new NoSanitizerException("No sanitizer available.");
			}
			$value = $aInput;
			foreach($this->filter as $filter) {
				$value = $filter->input($value);
			}
			return $value;
		}

		/**
		 * data výstupu, která jdou směrem k uživateli; zpravidla platí, že tato data musí být konvertovatelná zpět pomocí metody {@see self::input()}
		 *
		 * @param mixed $aOutput
		 *
		 * @return mixed
		 *
		 * @throws NoSanitizerException
		 */
		public function output($aOutput) {
			if($this->filter === null) {
				throw new NoSanitizerException("No sanitizer available.");
			}
			$value = $aOutput;
			/**
			 * @var $filter IFilter
			 */
			foreach(array_reverse($this->filter) as $filter) {
				$value = $filter->output($value);
			}
			return $value;
		}
	}
