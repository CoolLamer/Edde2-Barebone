<?php
	namespace Edde2\Utils;

	/**
	 * Porovnávatelný objekt - definuje pouze metodu, která má vrátít true/false, pokud je objekt totožný se vstupní hodnotou.
	 */
	interface IComparable {
		/**
		 * vypočítá unikátní hash, kterým lze tento objekt identifikovat (víceméně ID stavu); používá se pro porovnávání
		 *
		 * @return string
		 */
		public function hashCode();

		/**
		 * porovná tento se vstupním objektem; měl by využít metodu hashCode pro vygenerování hasha stavu objektu
		 *
		 * @param IComparable $aComparable
		 *
		 * @return bool
		 */
		public function equal(IComparable $aComparable);
	}
