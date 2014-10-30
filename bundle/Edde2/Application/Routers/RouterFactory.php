<?php
	namespace Edde2\Application\Routers;

	use Edde2\Reflection\IClassLoader;
	use Nette\Application\IRouter;
	use Nette\Application\Routers\RouteList;

	/**
	 * Továrna na routy - z configu je možné vytvořit routy pouze pomocí názvu třídy (fungují injecty a další vychytávky).
	 */
	class RouterFactory extends RouteList implements IRouter {
		/**
		 * pro vytváření instancí rout je zde dostupný ClassLoader
		 *
		 * @var IClassLoader
		 */
		private $classLoader;

		public function __construct(IClassLoader $aClassLoader, RouterConfig $aRouterConfig) {
			$this->classLoader = $aClassLoader;
			foreach($aRouterConfig as $router) {
				$this->register($router);
			}
		}

		public function register($aRouterClass) {
			$argz = func_get_args();
			array_shift($argz);
			$this[] = $this->classLoader->create($aRouterClass, $argz);
		}
	}
