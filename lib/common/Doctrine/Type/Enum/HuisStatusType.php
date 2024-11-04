<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\HuisStatus;

class HuisStatusType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return HuisStatus::class
	 */
	public function getEnumClass()
	{
		return HuisStatus::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumHuisStatus'
	 */
	public function getName(): string
	{
		return 'enumHuisStatus';
	}
}
