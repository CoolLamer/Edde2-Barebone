<?php
	namespace Edde2\Security;

	use Edde2\Object;
	use Nette\Security\IAuthenticator;

	class Authenticator extends Object implements IAuthenticator {
		public function authenticate(array $aCredentials) {
			throw new CredentialsException('To be reimplemented :).');
//			list($login, $password) = $aCredentials;
//			try {
//				$user = $this->modelService->modelUser()->load($login);
//				if($user->hasTokenValue()) {
//					if($user->getToken() !== $password) {
//						throw new TokenException('Bad token.');
//					}
//				} else if(Passwords::verify($password, $user->getPassword()) === false) {
//					throw new WrongPasswordException('Bad password.');
//				}
//				$roles = array();
//				foreach($user->collectionRoleFromUserRole() as $role) {
//					$roles[] = $role->getName();
//				}
//				return new Identity($user->getId(), $roles);
//			} catch(EmptyResultException $e) {
//				throw new CredentialsException("Cannot authenticate by given credentials [".(implode(', ', $aCredentials))."]");
//			} catch(CredentialsException $e) {
//				throw new CredentialsException("Cannot authenticate by given credentials [".(implode(', ', $aCredentials))."]");
//			}
		}
	}
