<?php
	namespace Edde2\Database\Drivers;

	use Edde2\Database\DatabaseException;
	use Edde2\Database\ISupplementalDriver;
	use Edde2\Database\UnknownTypeException;
	use Nette\Database\Drivers\PgSqlDriver as NettePgSqlDriver;

	class PgsqlDriver extends NettePgSqlDriver implements ISupplementalDriver {
		/**
		 * vezme PHP typ (libovolný, včetně objektů) a vrátí jeho databázovou itnerpretaci (tzn. pod jakým typem se má uložit do databáze)
		 *
		 * @param string $aPhpType
		 *
		 * @throws UnknownTypeException
		 *
		 * @return string
		 */
		public function typeToDatabase($aPhpType) {
			switch($aPhpType) {
				case 'string':
					return 'CHAR VARYING(255)';
				case 'text':
					return 'TEXT';
				case 'int':
					return 'INT';
				case 'xint':
					return 'BIGINT';
				case 'float':
					return 'FLOAT';
				case 'bool':
					return 'SMALLINT';
				case 'datetime':
					return 'TIMESTAMP';
			}
			throw new UnknownTypeException("Cannot translate '$aPhpType' to database type.");
		}

		public function exception(\PDOException $aException) {
			return new DatabaseException($aException->getMessage(), 0, $aException);
		}
	}
