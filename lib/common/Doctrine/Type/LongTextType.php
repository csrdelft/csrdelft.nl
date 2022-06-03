<?php


namespace CsrDelft\common\Doctrine\Type;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LongTextType extends Type {
	/**
	 * @inheritDoc
	 */
	public function getSQLDeclaration(array $column, AbstractPlatform $platform) {
		return 'LONGTEXT COMMENT \'(DC2Type:longtext)\'';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'longtext';
	}
}
