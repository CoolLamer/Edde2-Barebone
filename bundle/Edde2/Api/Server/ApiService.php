<?php
	namespace Edde2\Api\Server;

	use Edde2\Api\AccessException;
	use Edde2\Api\MissingParameterException;
	use Edde2\Api\UnknownMethodException;
	use Edde2\Caching\Cache;
	use Edde2\Model\ModelService;
	use Edde2\Object;
	use Edde2\Security\User;

	/**
	 * Formální předek všech služeb publikovaných do API.
	 *
	 * Metody, které přijímají rozvinuté parametry jsou jedinou výjimkou v systému, kde nedodržuji aNázev konvenci, ale pouze čistý název, poněvadž se reflexí
	 * veřejně publikují; aby uživatelé API nemuseli dodržovat tuto konvenci, musel jsem ji opustit i v API.
	 */
	abstract class ApiService extends Object implements IApiService {
		/**
		 * @var ServerService
		 */
		private $serverService;
		/**
		 * @var ModelService
		 */
		private $modelService;
		/**
		 * @var User
		 */
		private $user;
		/**
		 * @var Cache
		 */
		private $cache;

		final public function __construct(ServerService $aServerService, ModelService $aModelService, User $aUser, Cache $aCache) {
			$this->serverService = $aServerService;
			$this->modelService = $aModelService;
			$this->user = $aUser;
			$this->cache = $aCache;
		}

		/**
		 * @return ModelService
		 */
		final public function getModelService() {
			return $this->modelService;
		}

		/**
		 * @return User
		 */
		final public function user() {
			return $this->user;
		}

		/**
		 * @return ServerService
		 */
		final public function server() {
			return $this->serverService;
		}

		public function name() {
			return $this->reflection->getShortName();
		}

		/**
		 * vrátí jméno zdroje (ACL) pro tuto službu
		 *
		 * @return string
		 */
		public function resourceName() {
			return 'Api.'.$this->name();
		}

		public function checkAccess() {
			if(!$this->user->can($this->resourceName())) {
				throw new AccessException(sprintf('Requested service [%s]: Access denied for current user [%s].', $this->name(), $this->user->getModel(false)->getName()));
			}
		}

		public function checkMethodAccess($aMethod) {
			$methods = $this->methods();
			if(!in_array($aMethod, $methods)) {
				throw new UnknownMethodException(sprintf('Cannot get arguments for unknown method [%s::%s].', $this->name(), $aMethod));
			}
		}

		/**
		 * vrátí seznam metod, které tato služba publikuje
		 *
		 * @return string[]
		 */
		public function methods() {
			$this->checkAccess();
			if(($methodList = $this->cache->load($cacheId = $this->cacheId('method-list'))) === null) {
				foreach($this->reflection->getMethods() as $method) {
					if(!$method->hasAnnotation('api')) {
						continue;
					}
					$methodList[] = $method->getName();
				}
				$this->cache->save($cacheId, $methodList);
			}
			$resource = $this->resourceName();
			return array_filter($methodList, function ($aMethod) use ($resource) {
				return $this->user->can($resource, $aMethod, false);
			});
		}

		public function arguments($aMethod) {
			$this->checkMethodAccess($aMethod);
			if(($argz = $this->cache->load($cacheId = $this->cacheId('arg-list'.$aMethod))) !== null) {
				return $argz;
			}
			$method = $this->reflection->getMethod($aMethod);
			$argz = array();
			foreach($method->getParameters() as $arg) {
				$argz[] = $arg->getName();
			}
			return $this->cache->save($cacheId, $argz);
		}

		/**
		 * zavolá zadanou metodu nad touto službou; toto volání je třeba brát jako autorizované (tzn. není potřeba ověřovat, zda uživatel má právo)
		 *
		 * @param string $aMethod
		 * @param array $aArgz
		 *
		 * @throws MissingParameterException
		 * @throws UnknownMethodException
		 *
		 * @return mixed
		 */
		public function call($aMethod, array $aArgz = array()) {
			$this->checkMethodAccess($aMethod);
			$method = $this->reflection->getMethod($aMethod);
			$argz = $this->arguments($aMethod);
			$missings = array_diff($argz, array_keys($aArgz));
			if(!empty($missings)) {
				throw new MissingParameterException(sprintf('Missing argument(s) (%s) for requested method %s::%s(%s).', implode(', ', $missings), $this->name(), $aMethod, implode(', ', $argz)));
			}
			$argz = array_flip($argz);
			return $method->invokeArgs($this, array_merge($argz, $aArgz));
		}
	}
