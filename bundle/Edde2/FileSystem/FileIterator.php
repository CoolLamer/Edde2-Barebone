<?php
	namespace Edde2\FileSystem;

	use Edde2\Object;

	class FileIterator extends Object implements \Iterator {
		private $file;
		private $line;

		public function __construct(File $aFile) {
			$this->file = $aFile;
		}

		public function current() {
			return $this->line;
		}

		public function next() {
		}

		public function key() {
			return null;
		}

		public function valid() {
			return $this->line = $this->file->read();
		}

		public function rewind() {
			if(!$this->file->isOpen()) {
				$this->file->openForRead();
			}
			$this->file->rewind();
		}
	}
