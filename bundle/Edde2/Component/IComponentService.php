<?php
	namespace Edde2\Component;

	use Nette\ComponentModel\IContainer;

	interface IComponentService {
		/**
		 * vytvoří komponentu - nebinduje rodiče ani jméno - o to se musí postarat volající
		 *
		 * @param string $aComponent jméno komponenty, dle kterého se má vytvořit
		 * @param IContainer $aParent
		 * @param string $aName
		 *
		 * @return Control nenabindovaná komponenta (tzn. pouze má připravené závislosti, bez jména a rodiče (jak westernovka))
		 */
		public function create($aComponent, IContainer $aParent, $aName);

		/**
		 * zaregistruje poskytovatele komponent
		 *
		 * @param IComponentProvider $aComponentProvider
		 *
		 * @return void
		 */
		public function register(IComponentProvider $aComponentProvider);
	}
