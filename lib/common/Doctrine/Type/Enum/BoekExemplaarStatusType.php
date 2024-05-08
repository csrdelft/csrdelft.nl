<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\bibliotheek\BoekExemplaarStatus;

class BoekExemplaarStatusType extends EnumType
{
	public function getEnumClass(): string
	{
		return BoekExemplaarStatus::class;
	}

	public function getName(): string
	{
		return 'enumBoekExemplaarStatus';
	}
}
