<?php
	namespace Edde2\Reflection;

	interface IClassLoader {
		/**
		 * vytvoří novou instanci zadané třídy - musí být dohledatelná v indexu locatoru; instance je vytvořena reflexí a zavolají se veškeré injecty (tzn. konstruktor + inject metody)
		 *
		 * @param string $aClass vyhledávací řetězec třídy k vytvoření
		 * @param array $aArgz parametry konstruktoru třídy (aplikuje se autowiring)
		 * @param bool $aAllowAccessor povolit automatické vytváření instance pomocí accessoru, pokud je funcke podporována class loaderem
		 * @param bool $aResolveInterface
		 *
		 * @return object vrátí instanci požadované třídy
		 */
		public function create($aClass, array $aArgz = array(), $aAllowAccessor = true, $aResolveInterface = true);

		public function rebuild();

		/**
		 * řekne, zda je daná třída implementace zadaného interface; obojí se prožene findem, tzn. lze zadat část názvu třídy/regulár
		 *
		 * @param string $aClazz
		 * @param string $aInterface
		 *
		 * @throws MultipleClassFoundException
		 * @throws NotInterfaceException
		 * @throws NothingFoundException
		 *
		 * @return bool
		 */
		public function isImplementor($aClazz, $aInterface);

		/**
		 * najde dle zadaného fragmentu (regulární výraz)
		 *
		 * @param string $aClass
		 * @param bool $aResolveInterface autmaticky vrátí implementaci daného interface nebo výjimku
		 *
		 * @return string
		 */
		public function find($aClass, $aResolveInterface = true);
	}
