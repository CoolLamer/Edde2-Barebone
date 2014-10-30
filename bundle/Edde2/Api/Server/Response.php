<?php
	namespace Edde2\Api\Server;

	use Edde2\Utils\ObjectEx;

	class Response extends ObjectEx {
		public function __construct() {
			$this->property('jsonrpc', '2.0');
		}

		public function setId($aId) {
			$this->property('id', $aId);
			return $this;
		}

		public function setResult($aResult) {
			$this->property('result', $aResult);
			return $this;
		}

		public function setError(\Exception $aException) {
			$this->setId(null);
			$this->property('error', array(
				'code' => $aException->getCode(),
				'message' => $aException->getMessage(),
			));
			return $this;
		}
	}
