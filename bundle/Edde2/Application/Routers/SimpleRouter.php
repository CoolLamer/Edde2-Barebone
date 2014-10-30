<?php
	namespace Edde2\Application\Routers;

	use Nette\Application\Request;
	use Nette\Application\Routers\SimpleRouter as NetteSimpleRouter;
	use Nette\Http\IRequest;

	class SimpleRouter extends NetteSimpleRouter {
		public function match(IRequest $aHttpRequest) {
			if($aHttpRequest->getUrl()->getPathInfo() !== '') {
				return null;
			}
			// combine with precedence: get, (post,) defaults
			$params = $aHttpRequest->getQuery();
			$params += $this->defaults;
			if(!isset($params[self::PRESENTER_KEY]) || !is_string($params[self::PRESENTER_KEY])) {
				return null;
			}
			$module = null;
			if(isset($params[self::MODULE_KEY])) {
				$module = $params[self::MODULE_KEY].':';
			}
			$presenter = $module.$params[self::PRESENTER_KEY];
			unset($params[self::PRESENTER_KEY]);
			unset($params[self::MODULE_KEY]);
			return new Request($presenter, $aHttpRequest->getMethod(), $params, $aHttpRequest->getPost(), $aHttpRequest->getFiles(), array(Request::SECURED => $aHttpRequest->isSecured()));
		}
	}
