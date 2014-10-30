<?php
	namespace Edde2\Sanitizer;

	use Edde2\Object;

	abstract class Filter extends Object implements IFilter {
		/**
		 * na vstupu je hodnota směrem do aplikace (tzn. zpravidla sanitizace před validací a uložením do databáze)
		 *
		 * @param mixed $aInput
		 *
		 * @return mixed
		 */
		public function input($aInput) {
			return $aInput;
		}

		/**
		 * data výstupu, která jdou směrem k uživateli; zpravidla platí, že tato data musí být konvertovatelná zpět pomocí metody {@see self::input()}
		 *
		 * @param mixed $aOutput
		 *
		 * @return mixed
		 */
		public function output($aOutput) {
			return $aOutput;
		}
	}
