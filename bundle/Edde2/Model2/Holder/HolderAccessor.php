<?php
	namespace Edde2\Model2\Holder;

	use Edde2\DI\IAccessor;
	use Edde2\Model2\ModelService;
	use Edde2\Object;

	abstract class HolderAccessor extends Object implements IAccessor {
		/**
		 * @var ModelService
		 */
		private $modelService;

		public function __construct(ModelService $aModelService) {
			$this->modelService = $aModelService;
		}

		/**
		 * @param string $aName
		 *
		 * @return Holder
		 */
		protected function getHolder($aName) {
			return $this->modelService->createHolder($aName, false);
		}
	}
