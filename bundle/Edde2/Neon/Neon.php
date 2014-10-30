<?php
	namespace Edde2\Neon;

	use Edde2\FileSystem\FilePathNotFoundException;
	use Edde2\FileSystem\FileSystem;
	use Nette\Neon\Neon as NetteNeon;

	class Neon extends NetteNeon {
		public static function decode($aInput) {
			try {
				return parent::decode(FileSystem::content($aInput));
			} catch(FilePathNotFoundException $e) {
			}
			return parent::decode($aInput);
		}
	}
