<?php
	namespace Edde2\Utils\Process;

	use Edde2\Object;

	/**
	 * Parametr spouštěného procesu pro třídu Process.
	 */
	class Argument extends Object {
		/**
		 * @var string
		 *
		 * argument
		 */
		private $argument;
		/**
		 * @var string
		 *
		 * hodnota argumentu
		 */
		private $value;
		/**
		 * @var string
		 *
		 * formát výstuponího argumentu (ve výchozím stavu --argument "hodnota"
		 */
		private $format = '--%s "%s"';

		public function __construct($aArgument, $aValue = null, $aFormat = null) {
			$this->argument = trim($aArgument);
			$this->value = trim($aValue);
			if($aFormat !== null) {
				$this->format = $aFormat;
			}
		}

		public function format() {
			$argz = array($this->format, $this->argument, $this->value ? addslashes($this->value) : null);
			$argz = array_filter($argz);
			return call_user_func_array('sprintf', $argz);
		}

		public function __toString() {
			return $this->format();
		}
	}
