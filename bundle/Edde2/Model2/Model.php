<?php
	namespace Edde2\Model2;

	use Edde2\Model2\Config\Config;
	use Edde2\Model2\Config\Property;
	use Edde2\Model2\Holder\Holder;
	use Edde2\Model2\Query\Query;
	use Edde2\Utils\NoPropertiesException;
	use Edde2\Utils\ObjectEx;
	use Edde2\Utils\UnknownPropertyException;

	abstract class Model extends ObjectEx {
		/**
		 * @var Holder
		 */
		private $holder;
		/***
		 * @var Config
		 */
		private $config;
		/**
		 * @var bool
		 */
		private $remove;
		/**
		 * @var Model[]
		 */
		protected $bind = array();
		/**
		 * @var Model[][]
		 */
		protected $list = array();

		public function __construct(Holder $aHolder, Config $aModelConfig) {
			$this->holder = $aHolder;
			$this->config = $aModelConfig;
		}

		/**
		 * @return Config
		 */
		public function config() {
			return $this->config;
		}

		/**
		 * zkratka pro získání jména modelu
		 *
		 * @return string
		 */
		public function getModelName() {
			return $this->config()->getName();
		}

		/**
		 * vrátí konfiguraci požadované vlastnosti modelu
		 *
		 * @param string $aProperty
		 *
		 * @return Property
		 */
		public function getModelProperty($aProperty) {
			return $this->config->getProperty($aProperty);
		}

		public function setId($aId) {
			if(($id = $this->get('id')) !== null && !$this->isRemove()) {
				throw new ModelException("Model [".$this->getModelName()."] id [".$id."] has been already set.");
			}
			$this->set('id', $aId);
			return $this;
		}

		public function getId() {
			return $this->get('id');
		}

		public function hasId() {
			return $this->getId() !== null;
		}

		/**
		 * @param int|string $aResolve
		 *
		 * @return $this
		 */
		public function load($aResolve) {
			return $this->holder->query($this)->load($aResolve);
		}

		/**
		 * přabalující funkce, která pouze vyhodí místní výjimku a názvem modelu (v případě chybějící vlastnosti)
		 *
		 * @param string $aProperty
		 *
		 * @throws UnknownModelPropertyException
		 */
		public function check($aProperty) {
			try {
				parent::check($aProperty);
			} catch(UnknownPropertyException $e) {
				throw new UnknownModelPropertyException(sprintf("Requested property '%s' not found in model '%s'. Available properties: '%s'.", $aProperty, $this->getModelName(), implode(', ', $this->property())));
			} catch(NoPropertiesException $e) {
				throw new UnknownModelPropertyException(sprintf("Requested property '%s' not found in model '%s'. Model has no properties.", $aProperty, $this->getModelName()));
			}
		}

		public function current() {
			$current = parent::current();
			unset($current['id']);
			return $current;
		}

		public function change() {
			parent::change();
			$this->bind = array();
			$this->list = array();
			return $this;
		}

		public function hasBind() {
			return !empty($this->bind);
		}

		public function hasList() {
			return !empty($this->list);
		}

		/**
		 * @return Model[]
		 */
		public function getBindList() {
			return $this->bind;
		}

		/**
		 * @return Model[][]
		 */
		public function getList() {
			return $this->list;
		}

		/**
		 * označí tento model ke smazání
		 *
		 * @throws RemoveException
		 *
		 * @return $this
		 */
		public function remove() {
			if(!$this->hasId()) {
				throw new RemoveException("Cannot remove model without ID. It seems like deletion of new model.");
			}
			$this->remove = true;
			return $this;
		}

		/**
		 * řekne, zda je tento model označen ke smazání (=== true)
		 *
		 * @return bool
		 */
		public function isRemove() {
			return $this->remove === true;
		}

		/**
		 * zruší označení modelu ke smazání; má smysl pouze pokud je zavoláno před jeho zpracováním databází
		 *
		 * @return $this
		 */
		public function cancelRemoval() {
			$this->remove = false;
			return $this;
		}

		/**
		 * @return Holder
		 */
		public function holder() {
			return $this->holder;
		}

		/**
		 * @return $this
		 */
		public function save() {
			$this->holder->save($this);
			return $this;
		}

		/**
		 * @return Query
		 */
		abstract public function query();

		/**
		 * utility funkce, která převede danou kolekci na objekt; užitečné pro převod řádkové struktury (např. nastavení) do objektu
		 *
		 * @param Query|Model[] $aCollection Model[] je pouze IDE cheat (jelikož je Query iterovatelné, je to v určitém smyslu pravda
		 * @param string $aProperty
		 * @param string $aValue
		 *
		 * @return ObjectEx
		 */
		public static function collectionToObject($aCollection, $aProperty = 'property', $aValue = 'value') {
			$object = new ObjectEx();
			foreach($aCollection as $model) {
				$object->property($model->get($aProperty), $model->get($aValue));
			}
			return $object;
		}
	}
