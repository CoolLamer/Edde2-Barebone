<?php
	namespace Edde2\Application\Routers;

	use Nette\Application\Routers\Route;

	class CommonRouter extends Route {
		public function __construct() {
			parent::__construct('<module=Index>/<presenter=Index>/<action=index>[/<id>]');
		}
	}
