<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\Geslacht;

class GeslachtType extends EnumType
{
	public function getEnumClass(): string
	{
		return Geslacht::class;
	}

	public function getName(): string
	{
		return 'enumGeslacht';
	}
}
