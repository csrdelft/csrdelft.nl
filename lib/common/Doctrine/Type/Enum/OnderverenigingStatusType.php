<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\OnderverenigingStatus;

class OnderverenigingStatusType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return OnderverenigingStatus::class
	 */
	public function getEnumClass()
	{
		return OnderverenigingStatus::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumOnderverenigingStatus'
	 */
	public function getName(): string
	{
		return 'enumOnderverenigingStatus';
	}
}
