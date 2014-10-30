<?php
	namespace Edde2\Utils\Process;

	use Edde2\Object;

	class Process extends Object {
		private $bin;
		/**
		 * @var Argument[]
		 */
		private $argz = array();
		private $descriptor;
		private $pipez;
		private $stdin;
		private $stdout;
		private $stderr;
		private $cwd;
		private $async;

		public function __construct($aBin) {
			$this->bin = escapeshellcmd(trim($aBin));
			$this->descriptor = array(
				// stdin
				0 => array(
					'pipe',
					'r'
				),
				// stdout
				1 => array(
					'pipe',
					'w'
				),
				// stderr
				2 => array(
					'pipe',
					'w'
				),
			);
		}

		public function setStdIn($aStdIn) {
			$this->stdin = $aStdIn;
			return $this;
		}

		public function isAsync($aAsync = true) {
			$this->async = (bool)$aAsync;
			return $this;
		}

		public function arg($aArgument, $aValue = null, $aFormat = null) {
			$this->argz[] = new Argument($aArgument, $aValue, $aFormat);
			return $this;
		}

		public function argz(array $aArgz) {
			foreach($aArgz as $arg => $value) {
				$this->arg($arg, $value);
			}
			return $this;
		}

		public function setWorkingDir($aWorkingDir) {
			$this->cwd = $aWorkingDir;
			return $this;
		}

		/**
		 * sestaví spustitelný soubor a jeho argumenty
		 */
		public function format() {
			return sprintf('%s %s %s', $this->bin, implode(' ', $this->argz), $this->async === true ? '> /dev/null 2>&1 &' : null);
		}

		public function exec() {
			exec($this->format(), $output, $exit);
			if($this->async === true) {
				return true;
			}
			return $exit === 0;
		}

		public function run() {
			$handle = null;
			if(!is_resource($handle = proc_open($this->format(), $this->descriptor, $this->pipez, $this->cwd, null, array('bypass_shell' => true)))) {
				throw new CannotCreateProcessException("Cannot create process '".$this->bin."'.", $this->format());
			}
			if($this->stdin !== null) {
				fwrite($this->pipez[0], $this->stdin);
				$this->stdin = null;
			}
			$this->stdout = stream_get_contents($this->pipez[1]);
			$this->stderr = stream_get_contents($this->pipez[2]);
			foreach($this->pipez as $pipe) {
				fclose($pipe);
			}
			if(($result = proc_close($handle)) !== 0) {
				throw new ResultException($this->stderr, $this->format());
			}
			$this->pipez = array();
			return $this;
		}

		public function getStdOut() {
			return $this->stdout;
		}

		public function getStdErr() {
			return $this->stderr;
		}

		public function __toString() {
			return $this->format();
		}
	}
