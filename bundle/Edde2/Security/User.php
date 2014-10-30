<?php
	namespace Edde2\Security;

	use Nette\Security\User as NetteUser;

	class User extends NetteUser {
		public function getResourceString($aResource, $aPrivilege = null) {
			$chain = $aResource;
			if($aPrivilege !== null) {
				$chain .= ':'.$aPrivilege;
			}
			return $chain;
		}

		public function can($aResource, $aPrivilege = null, $aNeed = true) {
			try {
				$resource = explode(':', $aResource);
				if(!$this->isAllowed(reset($resource), $aPrivilege === null && count($resource) === 2 ? end($resource) : $aPrivilege)) {
					throw new AclException("Permission denied for '".$this->getResourceString($aResource, $aPrivilege)."'.", $this, $aResource, $aPrivilege);
				}
				return true;
			} catch(AclException $e) {
				if($aNeed === true) {
					throw $e;
				}
				return false;
			}
		}
	}
