<?php
	namespace Edde2\Utils;

	use Edde2\Sanitizer\Common\VoidFilter;
	use Edde2\Sanitizer\ISanitizer;
	use Edde2\Sanitizer\ISanitizerRule;
	use Edde2\Sanitizer\RuleNotFoundException;
	use Edde2\Validator\IValidatorList;
	use Edde2\Validator\RuleNotFoundException as ValidatorRuleNotFoundException;

	/**
	 * Obecný objekt, jehož smyslem je poskytnou komplexní funkci pro nastavování vlasntostí a sledování jejich změn. Jeho použití je velmi užitečné např. pro databázové modely,
	 * kdy tento objekt poskytuje veškeré potřebné funkce pro efektivní práci - jak sanitizaci vstupních (a dat na výstupu), tak jejich validaci (v případě registrace validátoru/filtru).
	 *
	 * Třída se snaží poskytnou maximálně jednoduché rozhraní, nicméně z důvodu úspory metod mohou některé být považovány za "magické" (jelikož se jedná o základní objekt, smyslem bylo
	 * eliminovat obsazování jmen funkcí, např. getProperties(), setProperties(), ....).
	 *
	 * Sledování změn využívá Comparable::compare(), pokud je hodnota stejná, jako výchozí, vyhodí se z pole změn (tzn. nastavování hodnot je inteligentní).
	 */
	class ObjectEx extends Comparable implements \IteratorAggregate {
		/**
		 * @var array
		 *
		 * jednoduché pole obsahující jména vlastností objektu (jmeno-vlastnosti)
		 */
		private $property = array();
		/**
		 * @var array
		 *
		 * pole obsahující jednotlivé hodnoty propert; jmeno-vlastnosti => hodnota
		 */
		private $value = array();
		/**
		 * @var array
		 *
		 * toto pole obsahuje hodnoty, které byly změněny (tzn. použitím standardních funkcí get/set)
		 */
		private $change = array();
		/**
		 * @var ISanitizer
		 *
		 * vstupně výstupní filtr vlastností; jakmile se nastaví jakákoli hodnota, prožene se filtrem
		 */
		private $sanitizer;
		/**
		 * @var IValidatorList
		 *
		 * validační služba; při nastavení hodnoty od uživatele projde sanitizací (pokud je dostupná) a validací (opět pokud je dostupná)
		 */
		private $validator;
		/**
		 * @var callable
		 *
		 * sada funkcí, které jsou připojené přímo k této instanci objektu (extensionMethod()); smyslem je umožnit nabindovat callable per objekt, nikoli staticky celému systému
		 */
		private $extend = array();

		public function __construct(array $aProperties = null) {
			if($aProperties !== null) {
				$this->propertyAll($aProperties);
			}
		}

		/**
		 * Pokud je předána instance, zaregistruje do tohoto objektu sanitizační (filtrační) službu. Pokud je předán NULL, službu
		 * odregistruje a pokud je zavolána bez parametrů, vrátí aktuálně nastavenou službu (nebo výjimku).
		 *
		 * @param ISanitizer $aSanitizer
		 *
		 * @return $this|ISanitizer
		 *
		 * @throws SanitizerNotSetException
		 */
		public function sanitizer(ISanitizer $aSanitizer = null) {
			if(func_num_args() === 0) {
				if($this->sanitizer === null) {
					throw new SanitizerNotSetException("Current object has no sanitizer.");
				}
				return $this->sanitizer;
			}
			$this->sanitizer = $aSanitizer;
			return $this;
		}

		/**
		 * vrátí sanitizační pravidlo pro danou propertu; pokud neexistuje, vrátí VoidFilter (filtr neměnící hodnoty)
		 *
		 * @param string $aProperty
		 *
		 * @return ISanitizerRule
		 */
		public function sanitize($aProperty) {
			try {
				if($this->sanitizer === null) {
					return new VoidFilter();
				}
				return $this->sanitizer->getRule($aProperty);
			} catch(RuleNotFoundException $e) {
				return new VoidFilter();
			}
		}

		/**
		 * pokud je předána instance, nastaví validátor, pokud NULL, odregistruje jej, pokud je voláno bez parametrů, vrátí aktuální
		 *
		 * @param IValidatorList $aValidator
		 *
		 * @return $this|IValidatorList
		 *
		 * @throws ValidatorNotSetException
		 */
		public function validator(IValidatorList $aValidator = null) {
			if(func_num_args() === 0) {
				if($this->validator === null) {
					throw new ValidatorNotSetException('Current object has no validator.');
				}
				return $this->validator;
			}
			$this->validator = $aValidator;
			return $this;
		}

		/**
		 * pokud je nastavený validátor s dostupnou sadou validačních pravidel pro danou položku, provede validaci (a případně vyhodí výjimku); pokud nic nastaveno není, tiše projde
		 *
		 * @param string $aProperty
		 * @param mixed $aValue
		 *
		 * @return self
		 */
		public function validateProperty($aProperty, $aValue) {
			if($this->validator === null) {
				return $this;
			}
			try {
				$this->validator->validate($aValue, $aProperty, $this);
			} catch(ValidatorRuleNotFoundException $e) {
			}
			return $this;
		}

		public function validate() {
			if($this->validator === null) {
				return $this;
			}
			/**
			 * validace běží nad celým validátorem, jinak by se nezkontrolovaly vlastnosti, které chybí
			 */
			foreach($this->validator as $property => $value) {
				$this->validateProperty($property, $this->get($property));
			}
			return $this;
		}

		/**
		 * funkce pro bezpečné ověření, zda je zadaná properta v objektu přítomná (=== true) nebo ne (=== false)
		 *
		 * @param string $aProperty
		 *
		 * @return bool
		 */
		public function has($aProperty) {
			return isset($this->property[$aProperty]);
		}

		/**
		 * zkonstroluje, zda je daná properta přítomná v objektu; pokud ne, vyhodí výjimku
		 *
		 * @param string $aProperty
		 *
		 * @throws NoPropertiesException
		 * @throws UnknownPropertyException
		 */
		public function check($aProperty) {
			$property = Strings::recamel($aProperty);
			if(empty($this->property)) {
				throw new NoPropertiesException('Current object has no properties set.');
			}
			if(!isset($this->property[$aProperty])) {
				throw new UnknownPropertyException(sprintf('Requested property "%s" not found in [%s].', $property, implode(', ', $this->property)), $aProperty, $this->property);
			}
		}

		/**
		 * přidá do objektu vlastnost a případně nastaví hodnotu (neprovádí se sanitizace ani validace)
		 *
		 * Pokud je na vstupu jeden string parametr, nastaví se právě jedna vlastnost; pokud je zadán i druhý, nastaví se jako vlastnost. V případě pole se nastaví pole hodnot a vlastností. Bez parametrů se vrátí aktuální pole vlastností.
		 *
		 * Funkce neprovádí žádnou kontrolu vstupních dat, hodí se proto na výchozí nastavení objektu.
		 *
		 * @param string|array|null $aProperty
		 * @param mixed|array|null $aValue
		 *
		 * @throws NoPropertiesException
		 * @throws PropertyException
		 *
		 * @return $this|array
		 */
		public function property($aProperty = null, $aValue = null) {
			if($aProperty === null) {
				if(empty($this->property)) {
					throw new NoPropertiesException('Current object has no properties.');
				}
				return $this->property;
			}
			$this->property[$aProperty] = $aProperty;
			$this->property = array_unique($this->property);
			$this->value[$aProperty] = $aValue;
			return $this;
		}

		public function propertyAll(array $aValues) {
			foreach($aValues as $property => $value) {
				$this->property($property, $value);
			}
			return $this;
		}

		/**
		 * přidá do objektu pole předaných vlastností; hodnoty budou nastavené na NULL
		 *
		 * @param array $aProperties
		 *
		 * @throws NoPropertiesException
		 *
		 * @return $this
		 */
		public function properties(array $aProperties) {
			foreach($aProperties as $property) {
				$this->property($property, null);
			}
			return $this;
		}

		public function cleanup() {
			$this->property = array();
			$this->value = array();
			$this->change = array();
			return $this;
		}

		/**
		 * debilně pojmenovaná funkce, nicméně ještě debilnější je obsazování hezkých názvů klíčovými slovy PHP; empty prostě použít nejde :(
		 *
		 * @return bool
		 */
		public function none() {
			return empty($this->property);
		}

		/**
		 * zjistí, zda se předávaná hodnota liší od původně nastavené
		 *
		 * @param string $aProperty jméno porovnávané vlastnosti
		 * @param mixed $aValue hodnota (musí být převoditelná na řetězec)
		 * @param bool $aCurrent pokud je true, porovnává aktuálně nastavenou hodnotu
		 *
		 * @return bool
		 *
		 * @throws NoPropertiesException
		 * @throws PropertyException
		 * @throws UnknownPropertyException
		 */
		public function diff($aProperty, $aValue, $aCurrent = false) {
			$this->check($aProperty);
			$value = $this->value[$aProperty];
			if($aCurrent === true) {
				$value = $this->get($aProperty);
			}
			return Comparable::compare($aValue, $value) === false;
		}

		/**
		 * nastaví ho objektu hodnotu; pokud je hodnota stejná, jako původně nastavená, odnastaví se z pole změněných vlastností; provádí se sanitizace a validace
		 *
		 * Funkce podporuje i možnost nastavit celý array (rozebere se a nastaví se jednotlivé vlastnosti). Funkce kontroluje přítomnost jednotlivých vlastností.
		 *
		 * @param string $aProperty
		 * @param mixed $aValue
		 *
		 * @return $this
		 *
		 * @throws NoPropertiesException
		 * @throws UnknownPropertyException
		 */
		public function set($aProperty, $aValue = null) {
			$this->check($aProperty);
			if(!$this->diff($aProperty, $aValue)) {
				unset($this->change[$aProperty]);
				return $this;
			}
			$this->validateProperty($aProperty, $value = $this->sanitize($aProperty)->input($aValue));
			$this->change[$aProperty] = $value;
			return $this;
		}

		public function setAll($aValues, $aSoft = true) {
			foreach($aValues as $k => $v) {
				if($aSoft === false || ($aSoft === true && $this->has($k))) {
					$this->set($k, $v);
				}
			}
			return $this;
		}

		/**
		 * nastaví do objektu hodnoty bez sanitizace a validace; kontroluje přítomnost vlastností
		 *
		 * @param string $aProperty
		 * @param mixed $aValue
		 *
		 * @throws NoPropertiesException
		 * @throws UnknownPropertyException
		 *
		 * @return $this
		 */
		public function put($aProperty, $aValue = null) {
			$this->putAll(array($aProperty => $aValue));
			return $this;
		}

		/**
		 * nastaví do objektu pole daných vlastností; kontroluje vlastnosti, ale neprovádí sanitizaci ani validaci
		 *
		 * @param array $aValues
		 *
		 * @throws NoPropertiesException
		 * @throws UnknownPropertyException
		 *
		 * @return $this
		 */
		public function putAll(array $aValues) {
			foreach($aValues as $k => $v) {
				$this->check($k);
			}
			foreach($aValues as $k => $v) {
				$this->value[$k] = $v;
			}
			return $this;
		}

		/**
		 * vrátí požadovanou hodnotu - pokud vlastnost neexistuje, bouchne
		 *
		 * @param string $aProperty
		 *
		 * @throws PropertyException
		 *
		 * @return self|mixed
		 */
		public function get($aProperty) {
			$this->check($aProperty);
			$value = $this->value[$aProperty];
			if(isset($this->change[$aProperty]) || array_key_exists($aProperty, $this->change)) {
				$value = $this->change[$aProperty];
			}
			return $this->sanitize($aProperty)->output($value);
		}

		public function getOrDefault($aProperty, $aDefault = null) {
			try {
				return $this->get($aProperty);
			} catch(PropertyException $e) {
			}
			return $aDefault;
		}

		/**
		 * vrátí aktuální pole vlastností a hodnot tohoto objektu; proběhne výstupní sanitizace
		 */
		public function getAll() {
			$values = array();
			foreach($this->property as $property) {
				$values[$property] = $this->get($property);
			}
			return $values;
		}

		public function reset($aProperty) {
			$this->check($aProperty);
			unset($this->change[$aProperty]);
			return $this;
		}

		/**
		 * řekne, zda se objekt změnil (tzn. byly nastaveny vlastnosti, které jsou rozdílné od výchozích)
		 *
		 * @param string|null $aProperty
		 *
		 * @throws NoPropertiesException
		 * @throws UnknownPropertyException
		 *
		 * @return bool
		 */
		public function changed($aProperty = null) {
			if($aProperty !== null) {
				$this->check($aProperty);
				return isset($this->change[$aProperty]) || array_key_exists($aProperty, $this->change);
			}
			return count($this->change) > 0;
		}

		/**
		 * vrátí změněné hodnoty objektu
		 *
		 * @return array
		 */
		public function changes() {
			return $this->change;
		}

		/**
		 * aplikuje změny objektu - tzn. probije změněné hodnoty do aktuálních hodnot (po tomto volání se objekt bude tvářit jako nezměněný)
		 *
		 * @return $this
		 */
		public function change() {
			$this->value = array_merge($this->value, $this->change);
			$this->change = array();
			return $this;
		}

		public function forceChange() {
			$this->change = $this->value;
			return $this;
		}

		/**
		 * vrátí aktuální stav objektu (tzn. včetně změněných hodnot) - neprovádí sanitizaci
		 *
		 * @return array
		 */
		public function current() {
			return array_merge($this->value, $this->change);
		}

		/**
		 * připojí do této instance objektu rozšiřující metodu
		 *
		 * @param string $aName
		 * @param callback $aCallback
		 *
		 * @return $this
		 */
		public function extend($aName, $aCallback) {
			$this->extend[$aName] = $aCallback;
			return $this;
		}

		/**
		 * explicitně zavolá rozšiřující metodu; pokud neexistuje, výjimka; přijímá dynamický počet parametrů, které se předají volanému callbacku - jako první
		 * parametr je vždy tento objekt
		 *
		 * @param string $aName
		 *
		 * @return mixed
		 *
		 * @throws ExtendNotFoundException
		 */
		public function func($aName) {
			if(!isset($this->extend[$aName])) {
				throw new ExtendNotFoundException("Requested extend method '$aName' not found.");
			}
			return call_user_func_array($this->extend[$aName], array_merge(array($this), array_slice(func_get_args(), 1)));
		}

		public function __call($aFunc, $aArgz = array()) {
			try {
				return call_user_func_array(array(
					$this,
					'func'
				), array_merge(array($aFunc), $aArgz));
			} catch(ExtendNotFoundException $e) {
			}
			$func = Strings::camel($aFunc);
			$item = Strings::recamel($aFunc, '-', 1);
			try {
				switch($func[0]) {
					case 'has':
						return $this->has($item);
					case 'set':
						return $this->set($item, ($value = reset($aArgz)) === false ? null : $value);
					case 'get':
						if(count($aArgz) === 0) {
							return $this->get($item);
						}
						return $this->getOrDefault($item, reset($aArgz));
				}
			} catch(UnknownPropertyException $e) {
			}
			return parent::__call($aFunc, $aArgz);
		}

		public function getIterator() {
			return new \ArrayIterator($this->current());
		}

		public function hashCode() {
			return Strings::hash($this->current());
		}
	}
