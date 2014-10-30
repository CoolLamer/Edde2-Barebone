<?php
	namespace Edde2\Utils;

	/**
	 * Konfigurace jednotlivého modelu.
	 */
	class Config extends ObjectEx {
		/**
		 * výchozí hodnoty, pokud nejsou nalezené v aktuální konfiguraci
		 *
		 * @var Config
		 */
		private $default;

		public function setDefault(array $aDefault) {
			$this->default = new ObjectEx($aDefault);
			return $this;
		}

		/**
		 * vrátí hodnotu konfigurace, pokud jsou nastavené výchozí hodnoty a není nalezena, vezme se výchoyí hodnota; pokud není ani výchozí, bouchne
		 *
		 * Pozn. get() s rozdílným počtem parametrů je použitý záměrně - pokud je přítomný pouze jeden parametr, ObjectEx vyhodí výjimku jinak použije výchozí hodnotu.
		 *
		 * @param string $aProperty
		 *
		 * @return ObjectEx|mixed
		 */
		public function value($aProperty) {
			if($this->default !== null) {
				return $this->getOrDefault($aProperty, $this->default->getOrDefault($aProperty));
			}
			return $this->get($aProperty);
		}
	}
