<?php
	namespace Edde2\Api;

	class EmptyRequestException extends ApiException {
		public function __construct($aMessage) {
			parent::__construct($aMessage, 1);
		}
	}
