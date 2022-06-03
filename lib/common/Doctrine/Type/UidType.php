<?php


namespace CsrDelft\common\Doctrine\Type;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UidType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'VARCHAR(4) COMMENT \'(DC2Type:uid)\'';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'uid';
    }
}
