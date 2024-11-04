<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\bibliotheek\BoekExemplaarStatus;

class BoekExemplaarStatusType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return BoekExemplaarStatus::class
	 */
	public function getEnumClass()
	{
		return BoekExemplaarStatus::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumBoekExemplaarStatus'
	 */
	public function getName(): string
	{
		return 'enumBoekExemplaarStatus';
	}
}
