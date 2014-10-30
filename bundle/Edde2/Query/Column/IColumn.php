<?php
	namespace Edde2\Query\Column;

	/**
	 * Když jde select from, může obsahovat všelicos; tento interface umožňuje různou implementaci prvku, který lze vybrat.
	 */
	interface IColumn {
		public function format($aEscapeCallback = null, $aSuppressAlias = false);

		/**
		 * vrátí textovou reprezentaci sloupce (nesmí být ovlivněno žádným escapováním)
		 *
		 * @return string
		 */
		public function column();

		/**
		 * pokud vrátí false, nebude alias generován, jakákoli jiná hodnota (string) bude použita jako alias (AS klauzulka)
		 *
		 * @return bool|string
		 */
		public function alias();

		/**
		 * vrátí true/false, pokud je nutné hodnotu escapovatů hodí se pro literal sloupce (např. NOW())
		 *
		 * @return bool
		 */
		public function escape();

		/**
		 * vrátí true/false, pokud se má před hodnotu přiřadit escapovaná tabulka; hodí se pro literal sloupce (např. SELECT NOW())
		 *
		 * @return bool
		 */
		public function table();

		/**
		 * pokud sloupec obsahuje nějaké argumenty (tzn. parametry dotazu), je možné je tímto sloučit; jinak vrátít prázdný array; tato metoda je přítomná z
		 * důvodu podpory vnořených dotazů
		 *
		 * @return array
		 */
		public function params();
	}
