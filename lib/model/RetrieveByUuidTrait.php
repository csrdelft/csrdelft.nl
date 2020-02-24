<?php


namespace CsrDelft\model;


use Doctrine\ORM\Mapping\ClassMetadata;

trait RetrieveByUuidTrait {
	public function retrieveByUuid($UUID) {
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(array_combine($metadata->getIdentifierFieldNames(), $primary_key_values));
	}
}
