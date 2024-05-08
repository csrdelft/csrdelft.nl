<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

abstract class EnumType extends Type
{
	protected $name;

	abstract public function getEnumClass();

	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		$values = array_map(function ($val) {
			return "'" . $val . "'";
		}, $this->getEnumClass()::getEnumValues());

		return sprintf(
			'ENUM(%s) COMMENT \'(DC2Type:%s)\'',
			implode(', ', $values),
			$this->getName()
		);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		if ($value == null) {
			return null;
		}

		$enumClass = $this->getEnumClass();
		return $enumClass::from($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		$enumClass = $this->getEnumClass();
		if ($value instanceof $enumClass) {
			return $value->getValue();
		} else {
			throw new InvalidArgumentException(
				print_r($value, true) . ' is not a ' . $this->getEnumClass()
			);
		}
	}

	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
