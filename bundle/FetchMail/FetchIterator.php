<?php
	namespace FetchMail;

	class FetchIterator implements \Iterator {
		/**
		 * @var Server
		 */
		private $server;
		/**
		 * @var resource
		 */
		private $stream;
		/**
		 * @var int
		 */
		private $messages;
		/**
		 * @var int
		 */
		private $current;
		private $item;

		public function __construct(Server $aServer) {
			$this->server = $aServer;
			$this->stream = $this->server->getImapStream();
			$this->messages = $this->server->getMessageCount();
		}

		/**
		 * @return Message
		 */
		public function current() {
			return $this->item;
		}

		public function next() {
			$this->item = new Message(imap_uid($this->stream, $this->current++), $this->server);
		}

		public function key() {
			return $this->current;
		}

		public function valid() {
			return $this->current <= $this->messages;
		}

		public function rewind() {
			$this->current = 1;
			$this->next();
		}
	}
