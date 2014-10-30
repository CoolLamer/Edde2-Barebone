<?php
	namespace Edde2\Utils;

	use Edde2\Object;

	abstract class Comparable extends Object implements IComparable {
		/**
		 * porovná tento se vstupním objektem; měl by využít metodu hashCode pro vygenerování hasha stavu objektu
		 *
		 * @param IComparable $aComparable
		 *
		 * @return bool
		 */
		public function equal(IComparable $aComparable) {
			return $this->hashCode() === $aComparable->hashCode();
		}

		/**
		 * pokusí se porovnat dvě vstupní hodnoty; pokud jsou obě {@see IComparable}, využije se {@see IComparable::equal()}, v jiném případě se porovnají objekty
		 * na základě stringové reprezentace (převod na string pomocí {@see Strings::string()})
		 *
		 * @param mixed|IComparable $aInputA
		 * @param mixed|IComparable $aInputB
		 *
		 * @return bool vrátí true, pokud jsou objekty stejné
		 */
		public static function compare($aInputA, $aInputB) {
			if($aInputA instanceof IComparable && $aInputB instanceof IComparable) {
				return $aInputA->equal($aInputB);
			}
			return Strings::string($aInputA) === Strings::string($aInputB);
		}
	}
