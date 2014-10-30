<?php
	namespace Edde2\Sanitizer\Common;

	use Edde2\Sanitizer\Filter;
	use Edde2\Security\User;
	use Nette\Utils\DateTime;

	/**
	 * Převádí datum a čas - jako řetězec (tzn. nikoli DateTime objekt).
	 *
	 * @auto-inject
	 */
	class DateTimeStringFilter extends Filter {
		/**
		 * je potřeba pro přístup k lokalizačním údajům
		 *
		 * @var User
		 */
		private $user;

		public function input($aInput) {
			return DateTime::from($aInput)->format('Y-m-d H:i:s');
		}

		public function output($aOutput) {
			return DateTime::from($aOutput)->format('d.m.Y H:i:s');
		}
	}
