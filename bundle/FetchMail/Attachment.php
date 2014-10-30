<?php
	/*
	 * This file is part of the Fetch package.
	 *
	 * (c) Robert Hafner <tedivm@tedivm.com>
	 *
		Copyright (c) 2009, Robert Hafner
		All rights reserved.

		Redistribution and use in source and binary forms, with or without
		modification, are permitted provided that the following conditions are met:
			* Redistributions of source code must retain the above copyright
			  notice, this list of conditions and the following disclaimer.
			* Redistributions in binary form must reproduce the above copyright
			  notice, this list of conditions and the following disclaimer in the
			  documentation and/or other materials provided with the distribution.
			* Neither the name of the Stash Project nor the
			  names of its contributors may be used to endorse or promote products
			  derived from this software without specific prior written permission.

		THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
		ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
		WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
		DISCLAIMED. IN NO EVENT SHALL Robert Hafner BE LIABLE FOR ANY
		DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
		(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
		LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
		ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
		(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
		SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	 */
	namespace FetchMail;

	/**
	 * This library is a wrapper around the Imap library functions included in php. This class wraps around an attachment
	 * in a message, allowing developers to easily save or display attachments.
	 */
	class Attachment {
		/**
		 * This is the structure object for the piece of the message body that the attachment is located it.
		 *
		 * @var \stdClass
		 */
		private $structure;
		/**
		 * This is the unique identifier for the message this attachment belongs to.
		 *
		 * @var int
		 */
		private $messageId;
		/**
		 * This is the ImapResource.
		 *
		 * @var resource
		 */
		private $imapStream;
		/**
		 * This is the id pointing to the section of the message body that contains the attachment.
		 *
		 * @var int
		 */
		private $partId;
		/**
		 * This is the attachments filename.
		 *
		 * @var string
		 */
		private $filename;
		/**
		 * This is the size of the attachment.
		 *
		 * @var int
		 */
		private $size;
		/**
		 * This stores the data of the attachment so it doesn't have to be retrieved from the server multiple times. It is
		 * only populated if the getData() function is called and should not be directly used.
		 *
		 * @internal
		 * @var array
		 */
		private $data;

		/**
		 * This function takes in an ImapMessage, the structure object for the particular piece of the message body that the
		 * attachment is located at, and the identifier for that body part. As a general rule you should not be creating
		 * instances of this yourself, but rather should get them from an ImapMessage class.
		 *
		 * @param Message $message
		 * @param \stdClass $structure
		 * @param string $partIdentifier
		 */
		public function __construct(Message $message, $structure, $partIdentifier = null) {
			$this->messageId = $message->getUid();
			$this->imapStream = $message->getImapBox()->getImapStream();
			$this->structure = $structure;
			if(isset($partIdentifier)) {
				$this->partId = $partIdentifier;
			}
			$parameters = Message::getParametersFromStructure($structure);
			if(isset($parameters['filename'])) {
				$this->filename = $parameters['filename'];
			} elseif(isset($parameters['name'])) {
				$this->filename = $parameters['name'];
			}
			$this->size = $structure->bytes;
			$this->mimeType = Message::typeIdToString($structure->type);
			if(isset($structure->subtype)) {
				$this->mimeType .= '/'.strtolower($structure->subtype);
			}
			$this->encoding = $structure->encoding;
		}

		/**
		 * This function returns the data of the attachment. Combined with getMimeType() it can be used to directly output
		 * data to a browser.
		 *
		 * @return string
		 */
		public function getData() {
			if(!isset($this->data)) {
				$messageBody = isset($this->partId) ? imap_fetchbody($this->imapStream, $this->messageId, $this->partId, FT_UID) : imap_body($this->imapStream, $this->messageId, FT_UID);
				$messageBody = Message::decode($messageBody, $this->encoding);
				$this->data = $messageBody;
			}
			return $this->data;
		}

		/**
		 * This returns the filename of the attachment, or false if one isn't given.
		 *
		 * @return string
		 */
		public function getFileName() {
			return (isset($this->filename)) ? $this->filename : false;
		}

		/**
		 * This function returns the mimetype of the attachment.
		 *
		 * @return string
		 */
		public function getMimeType() {
			return $this->mimeType;
		}

		/**
		 * This returns the size of the attachment.
		 *
		 * @return int
		 */
		public function getSize() {
			return $this->size;
		}

		/**
		 * This function returns the object that contains the structure of this attachment.
		 *
		 * @return \stdClass
		 */
		public function getStructure() {
			return $this->structure;
		}

		/**
		 * This function saves the attachment to the passed directory, keeping the original name of the file.
		 *
		 * @param  string $path
		 *
		 * @return bool
		 */
		public function saveToDirectory($path) {
			$path = rtrim($path, '/').'/';
			if(is_dir($path)) {
				return $this->saveAs($path.$this->getFileName());
			}
			return false;
		}

		/**
		 * This function saves the attachment to the exact specified location.
		 *
		 * @param  string $path
		 *
		 * @throws AttachmentException
		 *
		 * @return bool
		 */
		public function saveAs($path) {
			$dirname = dirname($path);
			if(file_exists($path)) {
				if(!is_writable($path)) {
					throw new AttachmentException(sprintf('File exists, but is not writeable: [%s].', $path));
				}
			} elseif(!is_dir($dirname) || !is_writable($dirname)) {
				throw new AttachmentException(sprintf("File's base dir is not dir, or is not writable: [%s].", $dirname));
			}
			if(($filePointer = fopen($path, 'w')) === false) {
				throw new AttachmentException(sprintf('Cannot open file for write: [%s].', $path));
			}
			switch($this->encoding) {
				case 3: //base64
					$streamFilter = stream_filter_append($filePointer, 'convert.base64-decode', STREAM_FILTER_WRITE);
					break;
				case 4: //quoted-printable
					$streamFilter = stream_filter_append($filePointer, 'convert.quoted-printable-decode', STREAM_FILTER_WRITE);
					break;
				default:
					$streamFilter = null;
			}
			$result = imap_savebody($this->imapStream, $filePointer, $this->messageId, $this->partId ?: 1, FT_UID);
			if($streamFilter) {
				stream_filter_remove($streamFilter);
			}
			fclose($filePointer);
			if($result !== true) {
				throw new AttachmentException(sprintf('imap_savebody failed (!== true) for file: [%s].', $path));
			}
		}
	}
