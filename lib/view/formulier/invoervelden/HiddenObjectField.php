<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;

class HiddenObjectField extends HiddenField
{

	private $entityType;

	public function __construct($name, $value, $type)
	{
		$entityManager = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		if ($value) {
			$metadata = $entityManager->getClassMetadata($type);
			$identifierFields = $metadata->getIdentifierFieldNames();

			if (count($identifierFields) != 1) {
				throw new CsrException('HiddenObjectField ondersteund geen composite id');
			}

			$identifier = $metadata->getIdentifierValues($value)[$identifierFields[0]];
		} else {
			$identifier = null;
		}


		parent::__construct($name, $identifier);
		$this->entityType = $type;
	}

	public function getFormattedValue()
	{
		$entityManager = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		if ($this->getValue()) {
			return $entityManager->getReference($this->entityType, $this->getValue());
		} else {
			return null;
		}
	}

}
