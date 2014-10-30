<?php
	namespace Edde2\Api\Server;

	use Edde2\Utils\ObjectEx;

	class Packet extends ObjectEx {
		public function getId() {
			return $this->getOrDefault('id');
		}

		public function getService() {
			if($this->has('service')) {
				return $this->get('service');
			}
			$method = explode('::', $this->get('method'));
			$this->property('service', $method[0]);
			$this->property('call', $method[1]);
			return $method[0];
		}

		public function getMethod() {
			if($this->has('call')) {
				return $this->get('call');
			}
			$method = explode('::', $this->get('method'));
			$this->property('service', $method[0]);
			$this->property('call', $method[1]);
			return $method[1];
		}

		public function getParams() {
			return (array)$this->getOrDefault('params', array());
		}

		public function setResponse(Response $aResponse) {
			$this->property('response', $aResponse);
			$aResponse->setId($this->getId());
			return $this;
		}

		public function getResponse() {
			return $this->get('response');
		}

		public function isNotify() {
			return !$this->has('id');
		}
	}
