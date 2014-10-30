<?php
	namespace Edde2\Caching;

	use Edde2\Utils\Arrays;
	use Nette\Caching\Cache as NetteCache;
	use Nette\Caching\IStorage;

	class Cache extends NetteCache {
		public function __construct(IStorage $aStorage, CacheConfig $aCacheConfig) {
			parent::__construct($aStorage, $aCacheConfig->getPrefix());
		}

		public function load($aKey, $aDefault = null) {
			if(($value = parent::load($aKey, null)) === null) {
				return $aDefault;
			}
			return $value;
		}

		public function saveTagged($aKey, $aData, $aTags = null) {
			return $this->save($aKey, $aData, array(self::TAGS => Arrays::forceArray($aTags)));
		}

		public function cleanTagged($aTags) {
			$this->clean(array(self::TAGS => Arrays::forceArray($aTags)));
			return $this;
		}
	}
