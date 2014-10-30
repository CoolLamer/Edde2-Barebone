<?php
	namespace Edde2\Bootstrap;

	use Edde2\FileSystem\FileSystem;

	/**
	 * Konfigurace jako služba, která poskytuje legální přístup k obecným nastavením systému (k parametrům).
	 */
	class CommonConfig extends Config {
		public function isConsoleMode() {
			return $this->get('consoleMode') === true;
		}

		public function isDebugMode() {
			return $this->get('debugMode') === true;
		}

		/**
		 * @return string
		 */
		public function getRootDir() {
			return FileSystem::absolutize($this->get('rootDir'));
		}

		/**
		 * @return string
		 */
		public function getAppDir() {
			return FileSystem::absolutize($this->get('appDir'));
		}

		/**
		 * @return string
		 */
		public function getWebDir() {
			return FileSystem::absolutize($this->get('wwwDir'));
		}

		/**
		 * @return string
		 */
		public function getLibDir() {
			return FileSystem::absolutize($this->get('libDir'));
		}

		/**
		 * @return string
		 */
		public function getTempDir() {
			return FileSystem::absolutize($this->get('tempDir'));
		}

		/**
		 * @return string
		 */
		public function getCacheDir() {
			return FileSystem::absolutize($this->get('cacheDir'));
		}
	}
