<?php
	namespace Edde2\FileSystem;

	use Edde2\Utils\Arrays;
	use Nette\Utils\FileSystem as NetteFileSystem;
	use Nette\Utils\Finder;

	class FileSystem extends NetteFileSystem {
		static public function absolutize($aPath) {
			if(!is_array($aPath)) {
				return FileSystem::realpath($aPath);
			}
			return array_map(function ($item) {
				return FileSystem::realpath($item);
			}, $aPath);
		}

		static public function content($aPath) {
			return file_get_contents(self::absolutize($aPath));
		}

		/**
		 * vrátí absolutní cestu (převede relativní odkazy v rámci cesty na kanonickou cestu); obal nad PHP realpath
		 *
		 * @param string $aPath
		 *
		 * @throws FilePathNotFoundException
		 *
		 * @return string
		 */
		static public function realpath($aPath) {
			if(($path = realpath($aPath)) === false) {
				throw new FilePathNotFoundException(sprintf('Cannot get real path from given string [%s].', $aPath));
			}
			return self::normalizePath($path);
		}

		/**
		 * vrátí normalizovaný formát cesty - pouze přehodí Windows-style lomítka (C:\Windows\System32 => C:/Windows/System32); cesta zůstane nepozměněna
		 *
		 * @param string $aPath
		 *
		 * @return string
		 */
		static public function normalizePath($aPath) {
			return str_replace('\\', '/', $aPath);
		}

		static public function tempFileName($aPrefix = null) {
			return tempnam(sys_get_temp_dir(), $aPrefix);
		}

		static public function checkFiles($aFileList) {
			foreach(Arrays::forceArray($aFileList) as $file) {
				if(!file_exists($file)) {
					throw new FileNotExistException("File '$file' not found.");
				}
			}
		}

		static public function filterAvailable($aFileList) {
			$list = array();
			foreach(Arrays::forceArray($aFileList) as $file) {
				if(file_exists($file)) {
					$list[] = self::absolutize($file);
				}
			}
			return $list;
		}

		/**
		 * smaže a znovu vytvoří předaný adresář
		 *
		 * @param string $aPath
		 */
		static public function recreate($aPath) {
			$permissions = 0777;
			if(file_exists($aPath)) {
				$permissions = self::permissions($aPath);
			}
			self::delete($aPath);
			self::createDir($aPath, $permissions);
		}

		static public function fetch($aPath, $aMask, $aExclude = array()) {
			return Finder::find($aMask)->from($aPath)->exclude($aExclude);
		}

		static public function permissions($aPath) {
			return (int)substr(sprintf('%o', fileperms($aPath)), -4);
		}

		/**
		 * sloučí dva adresáře do cíle (tzn. vykopíruje obsah zdroje do obsahu cíle)
		 *
		 * @param string $aSource
		 * @param string $aDestination
		 * @param bool $aRemoveSource
		 */
		static public function merge($aSource, $aDestination, $aRemoveSource = true) {
			/** @var $iterator \RecursiveDirectoryIterator */
			foreach($iterator = self::fetch($aSource, '*')->getIterator() as $file => $info) {
				self::copy($file, $aDestination.'/'.$iterator->getSubPathname());
			}
			if($aRemoveSource === true) {
				self::delete($aSource);
			}
		}

		static public function clean($aPath) {
			if(!is_dir($aPath)) {
				mkdir($aPath);
			}
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
				if($entry->isDir()) {
					@rmdir($entry);
				} else {
					@unlink($entry);
				}
			}
		}
	}
