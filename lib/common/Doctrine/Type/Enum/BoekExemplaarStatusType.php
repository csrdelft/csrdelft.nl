<?php


namespace CsrDelft\common\Doctrine\Type\Enum;


use CsrDelft\entity\bibliotheek\BoekExemplaarStatus;

class BoekExemplaarStatusType extends EnumType
{
	public function getEnumClass()
	{
		return BoekExemplaarStatus::class;
	}

	public function getName()
	{
		return 'enumBoekExemplaarStatus';
	}
}
