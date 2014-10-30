<?php
	namespace Edde2\Security;

	use Nette\Security\Permission;

	class Authorizator extends Permission {
		/**
		 * @var bool
		 */
		private $built;

		public function isAllowed($aRole = self::ALL, $aResource = self::ALL, $aPrivilege = self::ALL) {
			$this->build();
			return parent::isAllowed($aRole, $aResource, $aPrivilege);
		}

		public function build() {
			throw new SecurityException('To be reimplemented :)');
//			if($this->built === true) {
//				return;
//			}
//			foreach($this->modelService->collectionRole() as $role) {
//				$this->addRole($role->getName());
//			}
//			foreach($this->modelService->collectionResource() as $resource) {
//				$this->addResource($resource->getName());
//			}
//			foreach($this->modelService->collectionAcl() as $acl) {
//				$access = 'deny';
//				if($acl->isAccess()) {
//					$access = 'allow';
//				}
//				$argz = array($acl->getRole()->getName());
//				if($acl->hasResourceValue()) {
//					$argz[] = $acl->getResource()->getName();
//				}
//				if($acl->hasPrivilegeValue()) {
//					$argz[] = $acl->getPrivilege()->getName();
//				}
//				call_user_func_array(array(
//					$this,
//					$access
//				), $argz);
//			}
//			$this->built = true;
		}
	}
