<?php

namespace CsrDelft\common;


trait EnumTrait {
	/**
	 * @return string[]
	 */
	abstract public static function cases(): array;
	abstract public function getValue(): string;
	abstract public function getDescription(): string;
	public static function getEnumValues(): array {
		return array_column(self::cases(), 'value');
	}
	public static function isValidValue(string $value): bool {
		return in_array($value, self::getEnumValues(), strict: true);
	}

}
