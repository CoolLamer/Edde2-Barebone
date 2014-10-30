<?php
	namespace Edde2\Api\Server;

	use Edde2\Api\AccessException;
	use Edde2\Api\AmbiguousServiceNameException;
	use Edde2\Api\EmptyRequestException;
	use Edde2\Api\ServiceNotAvailableException;
	use Edde2\Caching\Cache;
	use Edde2\Object;
	use Edde2\Reflection\IClassLoader;
	use Edde2\Security\User;
	use Edde2\Utils\NoPropertiesException;
	use Edde2\Validator\ValidatorService;
	use Nette\Neon\Entity;

	class ServerService extends Object {
		/**
		 * @var ServerConfig
		 */
		private $serverConfig;
		/**
		 * @var ValidatorService
		 */
		private $validatorService;
		/**
		 * @var IClassLoader
		 */
		private $classLoader;
		/**
		 * @var User
		 */
		private $user;
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * seznam veřejně dostupných API
		 *
		 * @var IApiService[]
		 */
		private $service = array();

		final public function __construct(ServerConfig $aServerConfig, ValidatorService $aValidatorService, IClassLoader $aClassLoader, User $aUser, Cache $aCache) {
			$this->serverConfig = $aServerConfig;
			$this->validatorService = $aValidatorService;
			$this->classLoader = $aClassLoader;
			$this->user = $aUser;
			$this->cache = $aCache;
		}

		/**
		 * @param string $aName
		 *
		 * @throws AccessException
		 * @throws AmbiguousServiceNameException
		 * @throws ServiceNotAvailableException
		 *
		 * @return IApiService
		 */
		public function getService($aName) {
			$this->load();
			$available = $this->getServiceList();
			if(!in_array($aName, $available)) {
				if(isset($this->service[$aName])) {
					throw new AccessException(sprintf('Access denied for requested service [%s].', $aName));
				}
				throw new ServiceNotAvailableException(sprintf('Requested service [%s] is not available.', $aName));
			}
			return $this->service[$aName];
		}

		/**
		 * @return string[]
		 */
		public function getServiceList() {
			$this->load();
			return array_filter(array_keys($this->service), function ($aService) {
				return $this->user->can($this->service[$aService]->resourceName(), null, false);
			});
		}

		public function register(IApiService $aApiService) {
			if(isset($this->service[$aApiService->name()])) {
				throw new ServiceAlreadyRegisteredException(sprintf('Give service [%s] is already registered in server.', $aApiService->name()));
			}
			$this->service[$aApiService->name()] = $aApiService;
			return $this;
		}

		/**
		 * @param string $aClazz
		 *
		 * @throws UnknownApiServiceTypeException
		 *
		 * @return IApiService
		 */
		protected function createApiService($aClazz) {
			$clazz = $aClazz instanceof Entity ? $aClazz->value : $aClazz;
			$argz = $aClazz instanceof Entity ? $aClazz->attributes : array();
			$class = $this->classLoader->create($clazz, $argz);
			if(!($class instanceof IApiService)) {
				throw new UnknownApiServiceTypeException(sprintf('Given class [%s] is not instanceof IApiService. Api service must implement IApiService interface.', $clazz));
			}
			return $class;
		}

		protected function load() {
			if(!empty($this->service)) {
				return $this;
			}
			foreach($this->serverConfig->getServiceList() as $clazz) {
				$this->register($this->createApiService($clazz));
			}
			return $this;
		}

		/**
		 * @param string $aRequest
		 *
		 * @throws EmptyRequestException
		 *
		 * @return Request|Packet[]
		 */
		protected function createRequest($aRequest) {
			try {
				return $this->classLoader->create(Request::getReflection()->getName(), array($aRequest));
			} catch(NoPropertiesException $e) {
				throw new EmptyRequestException('Empty request. Do you want anything?');
			}
		}

		/**
		 * @param mixed $aResponse
		 *
		 * @return Response
		 */
		protected function createResponse($aResponse) {
			$response = new Response();
			if($aResponse instanceof \Exception) {
				$response->setError($aResponse);
				return $response;
			}
			$response->setResult($aResponse);
			return $response;
		}

		/**
		 * @param Packet $aPacket
		 *
		 * @return Response
		 */
		public function packet(Packet $aPacket) {
			$response = null;
			try {
				$response = $this->getService($aPacket->getService())->call($aPacket->getMethod(), $aPacket->getParams());
			} catch(\Exception $e) {
				$response = $e;
			}
			$aPacket->setResponse($response = $this->createResponse($response));
			return $response;
		}

		public function run($aRequest, $aUser, $aToken) {
			try {
				if(!$this->serverConfig->isAvailable()) {
					throw new ServerNotAvailableException('Server has no available services; API has been disabled.');
				}
				$this->user->login($aUser, $aToken);
				$this->load();
				$responseList = array();
				foreach($this->createRequest($aRequest) as $packet) {
					$response = $this->packet($packet);
					if($packet->isNotify()) {
						continue;
					}
					$responseList[] = $response->current();
				}
				$this->user->logout();
				return $responseList;
			} catch(EmptyRequestException $e) {
				return $this->createResponse($e)->current();
			}
		}
	}
