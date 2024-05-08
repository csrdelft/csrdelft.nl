<?php

namespace CsrDelft\common\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Door limitaties van InnoDB is de maximale lengte van een VARCHAR die ook een index of primary key heeft 191 karakters.
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/innodb-limits.html
 *
 * @package CsrDelft\common\Doctrine\Type
 */
class StringKeyType extends Type
{
	/**
	 * @inheritDoc
	 */
	public function getSQLDeclaration(array $column, AbstractPlatform $platform)
	{
		return 'VARCHAR(191) COMMENT \'(DC2Type:stringkey)\'';
	}

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return 'stringkey';
	}
}
