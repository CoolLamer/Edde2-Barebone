<?php
	namespace Edde2\Utils;

	use Nette\Reflection\ClassType;
	use Nette\Utils\Strings as NetteStrings;

	class Strings extends NetteStrings {
		/**
		 * rozparsuje zadaný řetězec podle velkých písmen a nepozměněná je vrátí jako array (např. getUserId vrátí [get, User, Id]); indexem lze ovlivnit
		 * počáteční položku
		 *
		 * @param string $aString
		 * @param int $aIndex
		 *
		 * @return array
		 */
		static public function camel($aString, $aIndex = 0) {
			$camel = preg_split('~(?=[A-Z])~', $aString, -1, PREG_SPLIT_NO_EMPTY);
			if($aIndex > 0) {
				return array_slice($camel, $aIndex);
			}
			return $camel;
		}

		/**
		 * rozebere vstupní řetězec dle velkých písmen a sloučí jej zpět pomocí oddělovače
		 *
		 * @param string $aString na tento řetězec bude aplikován camelize a camel
		 * @param string $aGlue jakým znakem má být řetězec spojen
		 * @param int $aIndex od jakého indexu
		 *
		 * @return string
		 */
		static public function recamel($aString, $aGlue = '-', $aIndex = 0) {
			$camel = self::camel($aString, $aIndex);
			if($aGlue === null) {
				foreach($camel as &$c) {
					$c = ucfirst($c);
				}
				return implode($aGlue, $camel);
			}
			return strtolower(implode($aGlue, $camel));
		}

		/**
		 * převede vstupní řetězec oddělený znakem do podoby s velkými písmeny; pokud není uveden separátor, funkce se jej pokusí sama detekovat (regulárem)
		 *
		 * @param string $aString
		 * @param string|null $aSeparator
		 * @param array $aSeparatorList
		 *
		 * @return mixed
		 */
		static public function camelize($aString, $aSeparator = null, $aSeparatorList = array(
				'|',
				':',
				'.',
				'-',
				'_'
			)) {
			if($aSeparator === null) {
				$aSeparator = $aSeparatorList;
			}
			// je potreba nahradit za nejaky znak, ktery rozezna NetteStrings::capitalize
			$aString = str_replace($aSeparator, '~', self::recamel($aString));
			return str_replace('~', null, self::capitalize($aString));
		}

		/**
		 * rychjlá funkce pro formátování částky (no, lepší asi je psát number_format, než String::nb(), ale alespoň je zapouzdřeno)
		 *
		 * @param float $aAmount
		 * @param int $aPrecision
		 * @param string $aThouand
		 * @param string $aDecimal
		 *
		 * @return string
		 */
		static public function nb($aAmount, $aPrecision = 2, $aThouand = ' ', $aDecimal = ',') {
			return number_format($aAmount, $aPrecision, $aDecimal, $aThouand);
		}

		/**
		 * rychlá funkce pro formátování datumu; pokud na vstupu nic není, vrátí aktuální datum; podporuje PHP DateTime
		 *
		 * @param string|\DateTime $aDate
		 * @param string $aFormat
		 *
		 * @return bool|string
		 */
		static public function dt($aDate, $aFormat = 'd.m.Y') {
			if($aDate instanceof \DateTime) {
				return $aDate->format($aFormat);
			} else if($aDate === null) {
				return date($aFormat);
			}
			return date($aFormat);
		}

		/**
		 * vypočítá hash na základě vstupu; pokud je zadán array solí, rekurzivně se vstup zahashuje s hodnotou soli
		 *
		 * @param mixed $aHash
		 * @param mixed $aSalt
		 * @param string $aAlgorithm
		 *
		 * @return string
		 */
		static public function hash($aHash, $aSalt = null, $aAlgorithm = 'sha512') {
			return hash($aAlgorithm, self::string($aHash).self::string($aSalt));
		}

		/**
		 * pokusí se dekódovat vstupní řetězec jako base64; pokud to nevyjde, vrátí se vstupní řetězec beze změny
		 *
		 * @param string $aPontentialBase64
		 *
		 * @return string
		 */
		static public function base64($aPontentialBase64) {
			$base64 = base64_decode($aPontentialBase64, true);
			if($base64 === false) {
				return $aPontentialBase64;
			}
			return $base64;
		}

		/**
		 * zjistí, zda je objekt možné převést na řetězec (včetně ověření implementace funkce __toString); na řetězec je míněna přímá konverze (tzn. (string)$aObject)
		 *
		 * @param mixed $aObject
		 *
		 * @return bool
		 */
		static public function stringable($aObject) {
			if(is_string($aObject)) {
				return true;
			} else if(is_scalar($aObject)) {
				return true;
			} else if(is_array($aObject)) {
				return false;
			} else if(is_null($aObject)) {
				return true;
			}
			$reflection = ClassType::from($aObject);
			return $reflection->hasMethod('__toString');
		}

		/**
		 * pokročilejší funkce pro převedení vstupu na řetězec
		 *
		 * Skalární vstup je vrácen přímo, null se vrátí jako null (nikoli 'null'), nad objektem se zjistí přítomnost funkce __toString, pokud vše selže, vstup se json_encode.
		 *
		 * @param mixed $aObject
		 *
		 * @return string
		 */
		static public function string($aObject) {
			if(is_bool($aObject)) {
				return $aObject ? 'true' : 'false';
			} else if(is_float($aObject)) {
				return self::nb($aObject);
			} else if(self::stringable($aObject)) {
				return (string)$aObject;
			}
			return json_encode($aObject, JSON_PRETTY_PRINT);
		}

		/**
		 * triviální funkce pro vyčištění CSSka (odstranění přebytečných znaků, mezer, ...)
		 *
		 * @param string $aSource
		 *
		 * @return string
		 */
		static public function minimizeCss($aSource) {
			$aSource = preg_replace('#\s+#', ' ', $aSource);
			$aSource = preg_replace('#/\*.*?\*/#s', '', $aSource);
			$aSource = str_replace(array(
				'; ',
				': ',
				' {',
				'{ ',
				', ',
				' ,',
				'} ',
				' }',
				';}',
			), array(
				';',
				':',
				'{',
				'{',
				',',
				',',
				'}',
				'}',
				'}',
			), $aSource);
			return trim($aSource);
		}

		static public function pregString($aUserString, $aDelimiter = '~') {
			return preg_quote($aUserString, $aDelimiter);
		}

		static public function upperLetters($aString, $aLower = false) {
			$string = '';
			foreach(self::camel(self::camelize($aString)) as $str) {
				$string .= $str[0];
			}
			return $aLower === true ? self::lower($string) : $string;
		}
	}
