<?php
	namespace Edde2\HealthMonitor;

	interface ILogger {
		public function lambda($aName, $aCallable);

		public function log($aEntry);

		public function info($aEntry);

		public function debug($aEntry);

		public function warning($aEntry);

		public function error($aEntry);

		public function fatal($aEntry);
	}
