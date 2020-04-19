<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\common\Enum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class EnumType extends Type {
	protected $name;

	/**
	 * @var Enum
	 */
	protected $enumClass;

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
		$values = array_map(function ($val) {
			return "'" . $val . "'";
		}, $this->enumClass::getEnumValues());

		return "ENUM(" . implode(", ", $values) . ")";
	}

	public function convertToPHPValue($value, AbstractPlatform $platform) {
		$enumClass = $this->enumClass;
		return new $enumClass($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		if ($value instanceof $this->enumClass) {
			return $value->getValue();
		} else {
			throw new \InvalidArgumentException("Value is not a " . $this->enumClass);
		}
	}

	public function getName() {
		return $this->name;
	}

	public function requiresSQLCommentHint(AbstractPlatform $platform) {
		return true;
	}
}
