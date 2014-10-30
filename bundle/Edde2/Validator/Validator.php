<?php
	namespace Edde2\Validator;

	use Edde2\Object;

	abstract class Validator extends Object implements IValidator {
		private $message;

		public function __construct($aMessage = 'Given value is not valid.') {
			$this->message = $aMessage;
		}

		/**
		 * ověří danou hodnotu a v případě, že není validní, vyhodí výjimku
		 *
		 * @param mixed $aValue
		 * @param mixed $aContext
		 *
		 * @throws ValueException
		 *
		 * @return self
		 */
		public function validate($aValue, $aContext = null) {
			if(!$this->isValid($aValue, $aContext)) {
				throw new ValueException($this->message);
			}
			return $this;
		}
	}
