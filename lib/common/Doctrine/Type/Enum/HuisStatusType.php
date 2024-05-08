<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\HuisStatus;

class HuisStatusType extends EnumType
{
	public function getEnumClass(): string
	{
		return HuisStatus::class;
	}

	public function getName(): string
	{
		return 'enumHuisStatus';
	}
}
