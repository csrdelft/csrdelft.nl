<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\GroepStatus;

class GroepStatusType extends EnumType
{
	public function getEnumClass(): string
	{
		return GroepStatus::class;
	}

	public function getName(): string
	{
		return 'enumGroepStatus';
	}
}
