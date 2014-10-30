<?php
	namespace Edde2\FileSystem;

	use Edde2\Utils\TreeObjectEx;
	use Nette\Neon\Neon;

	class NeonFile extends TextFile {
		public function read() {
			return Neon::decode(parent::read());
		}

		public function write($aString) {
			fwrite($this->getHandle(), Neon::encode($aString)."\n");
			return $this;
		}

		public function getContents() {
			return Neon::decode(parent::getContents());
		}
	}
