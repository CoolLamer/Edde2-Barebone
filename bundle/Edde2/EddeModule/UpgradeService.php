<?php
	namespace EddeModule;

	use Edde2\Object;

	class UpgradeService extends Object {
//	$currentConnection = $this->current->getConnection();
//			$mergeConnection = $this->merge->getConnection();
//			$currentConfigList = $this->current->getLoaderService()->getConfigList();
//			$mergeConfigList = $this->merge->getLoaderService()->getConfigList();
//			$currentContext = $currentConnection->context();
//			$tempFile = new TextFile(sprintf('%s/%s.sql', $this->commonConfig->getTempDir(), date('Y-m-d.H-i-s')));
//			$tempFile->openForWrite();
//			$sequence = array();
//			/** @var $config ModelConfig */
//			foreach(array_intersect_key($currentConfigList, $mergeConfigList) as $model => $config) {
//				if($config->isVirtual()) {
//					continue;
//				}
//				$sourceName = $config->getSourceName();
//				foreach($currentContext->table($sourceName)->order('id') as $row) {
//					$tempFile->write(Helpers::buildInsertSql($mergeConnection, $sourceName, $row->toArray()));
//					$sequence[$sourceName] = $row->id;
//				}
//			}
//			$tempFile->close();
	}
