<?php
	namespace Edde2\DI;

	use Edde2\Reflection\IClassLoader;
	use Nette\DI\Container as NetteContainer;
	use Nette\DI\MissingServiceException;
	use Nette\Reflection\ClassType;

	/**
	 * Systémový kontejner s některými novými drobnými vychytávkami - podporuje automatické
	 * volání injectu na základě přítomnosti anotace @inject nad třídou a další drobnosti, které
	 * se hodí.
	 */
	class Container extends NetteContainer {
		private $injected = array();

		/**
		 * regulárem vyhledá typ služby (tzn. vstupní dotaz jde přes typy služeb, nikoli názvy)
		 *
		 * @param string $aName
		 *
		 * @return object
		 */
		public function getService($aName) {
			try {
				$service = parent::getService($aName);
				if(!isset($this->injected[$aName]) && ($this->injected[$aName] = ClassType::from($service)->hasAnnotation('di')) === true) {
					$this->callInjects($service);
				}
				return $service;
			} catch(MissingServiceException $e) {
				$service = $this->getClassLoader()->create($aName);
				$this->addService($aName, $service);
				return $service;
			}
		}

		public function addService($aName, $aService) {
			parent::addService($aName, $aService);
			$this->meta[self::TYPES][strtolower(ClassType::from($aService)->getName())] = array($aName);
			return $this;
		}

		/**
		 * @return IClassLoader
		 */
		public function getClassLoader() {
			return $this->getByType('\\Edde2\\Reflection\\IClassLoader');
		}

		public function getByType($aClass, $aNeed = true) {
			$class = parent::getByType($aClass, $aNeed);
			/**
			 * getByType nevrací nic (void, potažmo null), proto není použit === operátor
			 */
			if($aNeed === false && !$class) {
				return $this->getService($aClass);
			}
			return $class;
		}
	}
