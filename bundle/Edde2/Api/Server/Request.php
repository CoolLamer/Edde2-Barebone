<?php
	namespace Edde2\Api\Server;

	use Edde2\Object;
	use Edde2\Utils\Arrays;
	use Edde2\Validator\ValidatorService;
	use Nette\Utils\Json;

	class Request extends Object implements \IteratorAggregate {
		/**
		 * @var ValidatorService
		 */
		private $validatorService;
		/**
		 * @var Packet[]
		 */
		private $stream = array();

		public function __construct($aSource, ValidatorService $aValidatorService) {
			$this->validatorService = $aValidatorService;
			$this->build($aSource);
		}

		public function build($aSource) {
			$packetValidator = $this->validatorService->get('Packet');
			foreach(Arrays::forceArray(Json::decode($aSource)) as $packet) {
				$this->stream[] = $packet = new Packet((array)$packet);
				$packet->validator($packetValidator);
				$packet->validate();
			}
		}

		public function getIterator() {
			return new \ArrayIterator($this->stream);
		}
	}
