<?php
	namespace Edde2\Validator\Common;

	use Edde2\Object;

	class CommonRules extends Object {
		public static function isIpv4($aIp) {
			return filter_var($aIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
		}

		public static function isIpv6($aIp) {
			return filter_var($aIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
		}
	}
