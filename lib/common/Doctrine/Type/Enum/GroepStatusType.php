<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\GroepStatus;

class GroepStatusType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return GroepStatus::class
	 */
	public function getEnumClass()
	{
		return GroepStatus::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumGroepStatus'
	 */
	public function getName(): string
	{
		return 'enumGroepStatus';
	}
}
