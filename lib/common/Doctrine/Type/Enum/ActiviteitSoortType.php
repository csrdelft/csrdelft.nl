<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\ActiviteitSoort;

class ActiviteitSoortType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return ActiviteitSoort::class
	 */
	public function getEnumClass()
	{
		return ActiviteitSoort::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumActiviteitSoort'
	 */
	public function getName(): string
	{
		return 'enumActiviteitSoort';
	}
}
