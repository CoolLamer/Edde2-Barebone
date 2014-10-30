<?php
	namespace Edde2\Component;

	use Nette\ComponentModel\IContainer;

	/**
	 * Třída sloužící jako poskytovatel komponent - na vstupu má třídu komponenty a její jméno (+ rodiče) a na výstupu
	 * nastavenou komponentu. Smyslem je umožnit různým částem systému generovat komponenty.
	 */
	interface IComponentProvider {
		/**
		 * zjistí, zda tento poskytovatel podporuje zadaný typ komponenty; pokud ne, hledá se další/výjimka
		 *
		 * @param string $aComponent
		 * @param string $aName
		 *
		 * @return bool
		 */
		public function probe($aComponent, $aName);

		/**
		 * vytvoří požadovanou komponentu
		 *
		 * @param string $aComponent
		 * @param IContainer $aParent
		 * @param string $aName
		 *
		 * @return Control
		 */
		public function create($aComponent, IContainer $aParent, $aName);
	}
