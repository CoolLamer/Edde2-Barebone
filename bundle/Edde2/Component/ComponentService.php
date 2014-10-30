<?php
	namespace Edde2\Component;

	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Reflection\NothingFoundException;
	use Nette\ComponentModel\IContainer;

	/**
	 * Smyslem této služby je poskytnout centralizovaný způsob vytváření komponent.
	 */
	class ComponentService extends Object implements IComponentService {
		/**
		 * @var IClassLoader
		 *
		 * vyhledá třídu s komponentou
		 */
		private $classLoader;
		/**
		 * registr poskytovatelů komponent
		 *
		 * @var IComponentProvider[]
		 */
		private $register = array();

		public function __construct(IClassLoader $aClassLoader) {
			$this->classLoader = $aClassLoader;
		}

		/**
		 * vytvoří komponentu
		 *
		 * @param string $aComponent
		 * @param IContainer $aParent
		 * @param string $aName
		 *
		 * @throws ComponentNotFoundException
		 * @throws \Exception
		 *
		 * @return Control
		 */
		public function create($aComponent, IContainer $aParent, $aName) {
			try {
				$provider = null;
				foreach($this->register as $provider) {
					if($provider->probe($aComponent, $aName)) {
						break;
					}
				}
				if($provider !== null) {
					return $provider->create($aComponent, $aParent, $aName);
				}
				return $this->classLoader->create($aComponent, array(
					$aParent,
					$aName
				));
			} catch(NothingFoundException $e) {
				if($aComponent !== $e->getQuery()) {
					throw $e;
				}
				throw new ComponentNotFoundException("Component '$aName' [class $aComponent] not found. Use this exception for alternative component creation (holds all necessaries).", $this, $aComponent, $aParent, $aName);
			}
		}

		/**
		 * zaregistruje poskytovatele komponent
		 *
		 * @param IComponentProvider $aComponentProvider
		 *
		 * @return void
		 */
		public function register(IComponentProvider $aComponentProvider) {
			$this->register[] = $aComponentProvider;
		}
	}
