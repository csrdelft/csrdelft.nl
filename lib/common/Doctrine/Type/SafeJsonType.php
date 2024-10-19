<?php

namespace CsrDelft\common\Doctrine\Type;

use CsrDelft\common\Doctrine\Type\Serializer\SafeJsonSerializer;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class SafeJsonType extends Type
{
	abstract protected function getAcceptedTypes();

	/**
	 * @inheritDoc
	 */
	public function getSQLDeclaration(
		array $fieldDeclaration,
		AbstractPlatform $platform
	): string {
		return sprintf('TEXT COMMENT \'(DC2Type:%s)\'', $this->getName());
	}
	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		if (!$value) {
			return $value;
		}
		$serializer = new SafeJsonSerializer($this->getAcceptedTypes());
		return $serializer->unserialize($value);
	}

	public function convertToDatabaseValue(
		$value,
		AbstractPlatform $platform
	): mixed {
		$serializer = new SafeJsonSerializer($this->getAcceptedTypes());
		return $serializer->serialize($value);
	}
}
