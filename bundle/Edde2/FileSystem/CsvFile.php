<?php
	namespace Edde2\FileSystem;

	class CsvFile extends TextFile {
		private $delimiter;
		private $enclosure;

		public function __construct($aFile, $aDelimiter = ';', $aEnclosure = '"') {
			parent::__construct($aFile);
			$this->delimiter = $aDelimiter;
			$this->enclosure = $aEnclosure;
		}

		public function read() {
			if(($line = fgetcsv($this->getHandle(), null, $this->delimiter, $this->enclosure)) === false && $this->isAutoClose()) {
				$this->close();
			}
			return $line;
		}

		/**
		 * @param array $aString
		 *
		 * @throws FileNotOpenedException
		 *
		 * @return $this
		 */
		public function write($aString) {
			fputcsv($this->getHandle(), $aString, $this->delimiter, $this->enclosure);
			return $this;
		}
	}
