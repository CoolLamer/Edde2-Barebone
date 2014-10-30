<?php
	namespace Edde2\FileSystem;

	use Nette\Utils\Json;

	class JsonFile extends TextFile {
		public function read() {
			return Json::decode(parent::read(), Json::FORCE_ARRAY);
		}

		public function write($aString) {
			fwrite($this->getHandle(), Json::encode($aString)."\n");
			return $this;
		}

		public function getContents() {
			return Json::decode(parent::getContents());
		}
	}
