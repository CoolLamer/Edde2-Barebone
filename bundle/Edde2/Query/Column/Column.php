<?php
	namespace Edde2\Query\Column;

	use Edde2\Object;
	use Edde2\Query\Table\Table;

	abstract class Column extends Object implements IColumn {
		private $column;
		private $alias;
		/**
		 * @var Table
		 */
		private $table;

		public function __construct($aColumn, $aAlias = false, Table $aTable = null) {
			$this->column = $aColumn;
			$this->alias = $aAlias;
			$this->table = $aTable;
		}

		public function format($aEscapeCallback = null, $aSuppressAlias = false) {
			$escape = $aEscapeCallback ?: function ($aString) {
				return escapeshellarg($aString);
			};
			$column = array();
			if($this->table && $this->table() === true) {
				$column[] = call_user_func($escape, $this->table->alias());
				$column[] = '.';
			}
			$name = $this->column();
			if($this->escape() === true) {
				$name = call_user_func($escape, $name);
			}
			$column[] = $name;
			if($aSuppressAlias !== true && ($alias = $this->alias()) !== false) {
				$column[] = ' AS ';
				$column[] = call_user_func($escape, $alias);
			}
			return implode('', $column);
		}

		public function column() {
			return $this->column;
		}

		public function alias() {
			return $this->alias;
		}

		/**
		 * vrátí true/false, pokud je nutné hodnotu escapovatů hodí se pro literal sloupce (např. NOW())
		 *
		 * @return bool
		 */
		public function escape() {
			return true;
		}

		/**
		 * vrátí true/false, pokud se má před hodnotu přiřadit escapovaná tabulka; hodí se pro literal sloupce (např. SELECT NOW())
		 *
		 * @return bool
		 */
		public function table() {
			return true;
		}

		/**
		 * pokud sloupec obsahuje nějaké argumenty (tzn. parametry dotazu), je možné je tímto sloučit; jinak vrátít prázdný array; tato metoda je přítomná z
		 * důvodu podpory vnořených dotazů
		 *
		 * @return array
		 */
		public function params() {
			return array();
		}
	}
