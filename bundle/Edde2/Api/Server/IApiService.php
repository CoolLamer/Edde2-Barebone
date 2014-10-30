<?php
	namespace Edde2\Api\Server;

	interface IApiService {
		/**
		 * vrátí jméno této služby, pod kterým se zaregistruje do serveru
		 *
		 * @return string
		 */
		public function name();

		/**
		 * vrátí jméno zdroje (ACL) pro tuto službu
		 *
		 * @return string
		 */
		public function resourceName();

		/**
		 * vrátí seznam metod, které tato služba publikuje
		 *
		 * @return string[]
		 */
		public function methods();

		/**
		 * @param string $aMethod
		 *
		 * @return string[]
		 */
		public function arguments($aMethod);

		/**
		 * zavolá zadanou metodu nad touto službou
		 *
		 * @param string $aMethod
		 * @param array $aArgz
		 *
		 * @return mixed
		 */
		public function call($aMethod, array $aArgz = array());
	}
