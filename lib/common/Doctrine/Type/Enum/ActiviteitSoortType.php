<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\ActiviteitSoort;

class ActiviteitSoortType extends EnumType
{
	public function getEnumClass()
	{
		return ActiviteitSoort::class;
	}

	public function getName(): string
	{
		return 'enumActiviteitSoort';
	}
}
