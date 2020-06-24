<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;

class LidObjectField extends LidField {
	public function __construct($name, $value, $description, $zoekin = 'alleleden') {
		parent::__construct($name, $value->uid ?? null, $description, $zoekin);
	}

	/**
	 * @return Profiel|null
	 */
	public function getFormattedValue() {
		if ($this->getValue()) {
			$entityManager = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

			return $entityManager->getReference(Profiel::class, $this->getValue());
		}

		return null;
	}

}
