<?php
	namespace Edde2\Model2\Import;

	use Edde2\Object;

	/**
	 * Díkz autowiringu je nutné tuto službu poslat do konstruktoru; pokud někde nemá proběhnout import dat, lze použít tuto třídu.
	 */
	class DummyImportService extends Object implements IImportService {
		public function import() {
		}
	}
