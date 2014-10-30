<?php
	namespace Edde2\Application\Responses;

	use Nette\Http\IRequest;
	use Nette\Http\IResponse;

	class TextResponse extends Response {
		private $response;
		private $contentType;

		public function __construct($aResponse = null, $aContentType = 'text/plain') {
			$this->response = $aResponse;
			$this->contentType = $aContentType;
		}

		public function send(IRequest $aHttpRequest, IResponse $aHttpResponse) {
			$aHttpResponse->setContentType($this->contentType);
			echo $this->response;
		}
	}
