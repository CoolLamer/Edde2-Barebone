<?php
	namespace Edde2\Utils;

	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;

	/**
	 * Obecný controller, který slouží zpravidla na obalení modelových objektů business logikou.
	 */
	abstract class Controller extends Object {
		/**
		 * @var IClassLoader
		 */
		private $classLoader;

		final public function injectClassLoader(IClassLoader $aClassLoader) {
			$this->classLoader = $aClassLoader;
		}

		public function createClass($aClass, array $aArgz = array()) {
			return $this->classLoader->create($aClass, $aArgz);
		}
	}
