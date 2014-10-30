<?php
	namespace Edde2\FileSystem;

	use Edde2\Object;

	abstract class File extends Object implements \IteratorAggregate {
		/**
		 * @var string
		 */
		private $file;
		/**
		 * @var resource
		 */
		private $handle;
		/**
		 * @var bool
		 */
		private $autoClose = true;

		public function __construct($aFile) {
			$this->file = $aFile;
		}

		public function getFullFileName() {
			return $this->file;
		}

		protected function open($aMode) {
			if($this->isOpen()) {
				throw new AlreadyOpenException(sprintf('Current file [%s] is already opened.', $this->file));
			}
			if($aMode === 'r') {
				$this->file = FileSystem::absolutize($this->file);
			}
			$this->handle = fopen($this->file, $aMode);
			return $this;
		}

		public function setAutoClose($aAutoClose) {
			$this->autoClose = (bool)$aAutoClose;
			return $this;
		}

		/**
		 * @return boolean
		 */
		public function isAutoClose() {
			return (bool)$this->autoClose;
		}

		public function isOpen() {
			return $this->handle !== null;
		}

		public function openForRead() {
			$this->open('r');
			return $this;
		}

		public function openForWrite() {
			$this->open('w');
			return $this;
		}

		public function rewind() {
			rewind($this->getHandle());
			return $this;
		}

		public function getContents() {
			return file_get_contents($this->file);
		}

		public function read() {
			if(($line = fgets($this->getHandle())) === false && $this->isAutoClose()) {
				$this->close();
			}
			return $line;
		}

		public function write($aString) {
			fwrite($this->getHandle(), "$aString\n");
			return $this;
		}

		protected function getHandle() {
			if(!$this->isOpen()) {
				throw new FileNotOpenedException(sprintf('Current file [%s] is not opened or has been already closed.', $this->file));
			}
			return $this->handle;
		}

		public function close() {
			fclose($this->getHandle());
			$this->handle = null;
			return $this;
		}

		public function getIterator() {
			return new FileIterator($this);
		}

		static public function mime($aFile) {
			return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $aFile);
		}

		static public function unzip($aZipArchive, $aPath) {
			$zip = new \ZipArchive();
			if($zip->open($aZipArchive) !== true) {
				throw new OpenException(sprintf('Cannot open ZIP archive [%s].', $aZipArchive));
			}
			$zip->extractTo($aPath);
			$zip->close();
		}
	}
