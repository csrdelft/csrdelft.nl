<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\OnderverenigingStatus;

class OnderverenigingStatusType extends EnumType
{
	public function getEnumClass(): string
	{
		return OnderverenigingStatus::class;
	}

	public function getName(): string
	{
		return 'enumOnderverenigingStatus';
	}
}
