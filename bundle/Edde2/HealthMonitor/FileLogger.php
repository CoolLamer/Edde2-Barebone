<?php
	namespace Edde2\HealthMonitor;

	use Edde2\FileSystem\FileSystem;
	use Tracy\Dumper;

	class FileLogger extends Logger {
		private $dir;
		private $flags = array('all' => true);

		public function __construct($aName, $aDir) {
			parent::__construct($aName);
			$this->dir = $aDir;
			$this->dir = sprintf('%s/%s', $this->dir, date('Y-m-d'));
			FileSystem::createDir($this->dir);
		}

		public function enableAll($aEnable = true) {
			$this->flags['all'] = (bool)$aEnable;
			return $this;
		}

		public function enableInfo($aEnable = true) {
			$this->flags['info'] = (bool)$aEnable;
			return $this;
		}

		public function enableDebug($aEnable = true) {
			$this->flags['debug'] = (bool)$aEnable;
			return $this;
		}

		public function enableWarning($aEnable = true) {
			$this->flags['warning'] = (bool)$aEnable;
			return $this;
		}

		public function enableError($aEnable = true) {
			$this->flags['error'] = (bool)$aEnable;
			return $this;
		}

		public function enableFatal($aEnable = true) {
			$this->flags['fatal'] = (bool)$aEnable;
			return $this;
		}

		/**
		 * @param string $aName
		 * @param callable $aCallable
		 *
		 * @throws \Exception pokud nastane výjimka ve volání, je přehozena výše
		 *
		 * @return mixed
		 */
		public function lambda($aName, $aCallable) {
			try {
				$this->info($aName);
				$result = call_user_func($aCallable);
				$this->info($result ?: 'success');
				return $result;
			} catch(\Exception $e) {
				$this->error($e);
				throw $e;
			}
		}

		protected function logEntry($aEntry, $aSeverity = null) {
			if(is_callable($aEntry)) {
				throw new CallableException('Cannot log callable. Use lambda() instead.');
			}
			if(isset($this->flags[$aSeverity]) && $this->flags[$aSeverity] !== true || $this->flags['all'] !== true) {
				return $this;
			}
			$log = sprintf("[%s :: %s %s]\n%s", $aSeverity ?: 'common', date('Y-m-d H:i:s'), str_pad('', 128, '=-'), Dumper::toText($aEntry));
			file_put_contents(sprintf('%s/%02s-%s.log', $this->dir, floor(date('H') / 8) * 8, $this->getName()), $log, FILE_APPEND);
			return $this;
		}

		public function log($aEntry) {
			return $this->logEntry($aEntry);
		}

		public function info($aEntry) {
			return $this->logEntry($aEntry, 'info');
		}

		public function debug($aEntry) {
			return $this->logEntry($aEntry, 'info');
		}

		public function warning($aEntry) {
			return $this->logEntry($aEntry, 'info');
		}

		public function error($aEntry) {
			return $this->logEntry($aEntry, 'info');
		}

		public function fatal($aEntry) {
			return $this->logEntry($aEntry, 'info');
		}
	}
