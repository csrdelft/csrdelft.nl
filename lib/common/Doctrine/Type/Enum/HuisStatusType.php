<?php


namespace CsrDelft\common\Doctrine\Type\Enum;


use CsrDelft\entity\groepen\enum\HuisStatus;

class HuisStatusType extends EnumType
{
	public function getEnumClass()
	{
		return HuisStatus::class;
	}

	public function getName()
	{
		return 'enumHuisStatus';
	}
}
