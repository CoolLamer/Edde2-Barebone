<?php
	namespace Edde2;

	use Nette\Object as NetteObject;

	/**
	 * @method bool acl(string $aResource, string $aPrivilege = null, $aNeed = true) zkontroluje oprávnění aktuálního uživatele;
	 * pokud selže, vyhodí výjimku
	 * @method array|mixed cacheId(string $aId)
	 */
	class Object extends NetteObject {
	}
