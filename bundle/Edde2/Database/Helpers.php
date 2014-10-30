<?php
	namespace Edde2\Database;

	use Nette\Database\Helpers as NetteHelpers;
	use Nette\Database\SqlPreprocessor;

	class Helpers extends NetteHelpers {
		static public function buildInsertSql(Connection $aConnection, $aTable, array $aValues) {
			$preprocessor = new SqlPreprocessor($aConnection);
			$sql = $preprocessor->process(array(
				$aConnection->context()->table($aTable)->getSqlBuilder()->buildInsertQuery(),
				$aValues
			));
			$params = $sql[1];
			$sql = preg_replace_callback('~\?~', function () use ($params, $aConnection) {
				static $i = 0;
				if(!isset($params[$i])) {
					return '?';
				}
				$param = $params[$i++];
				return $aConnection->quote($param);
			}, $sql[0]);
			return $sql;
		}
	}
