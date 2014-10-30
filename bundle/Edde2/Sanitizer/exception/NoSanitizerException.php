<?php
	namespace Edde2\Sanitizer;

	/**
	 * Pokud nejsou v pravidle registrované žádné sanitizátory, vyhodí se tato výjimka.
	 */
	class NoSanitizerException extends SanitizerException {
	}
