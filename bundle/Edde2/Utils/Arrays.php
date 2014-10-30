<?php
	namespace Edde2\Utils;

	use Nette\Utils\Arrays as NetteArrays;

	class Arrays extends NetteArrays {
		public static function forceArray($aInput) {
			if(empty($aInput)) {
				return array();
			}
			if(!is_array($aInput)) {
				return array($aInput);
			}
			return $aInput;
		}

		/**
		 * nastaví každé hodnotě pole NULL (pole musí být jednorozměrné); modifiuje vstupní pole
		 *
		 * @param array $aArray
		 *
		 * @return array
		 */
		public static function clear($aArray){
			foreach($aArray as &$v) {
				$v = null;
			}
			return $aArray;
		}
	}
