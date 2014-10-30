<?php
	namespace Edde2\Database;

	use Nette\Database\ISupplementalDriver as INetteSupplementalDriver;

	interface ISupplementalDriver extends INetteSupplementalDriver {
		/**
		 * vezme PHP typ (libovolný, včetně objektů) a vrátí jeho databázovou itnerpretaci (tzn. pod jakým typem se má uložit do databáze)
		 *
		 * @param string $aPhpType
		 *
		 * @throws UnknownTypeException
		 *
		 * @return string
		 */
		public function typeToDatabase($aPhpType);

		/**
		 * přeloží databázovou výjimku na konkrétní chybu; každý ovladač má jiný formát textu chyby (např. duplicita sloupců, neeixtující relace, ...)
		 *
		 * @param \PDOException $aException
		 *
		 * @return DatabaseException
		 */
		public function exception(\PDOException $aException);
	}
