<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\courant\CourantCategorie;

class CourantCategorieType extends EnumType
{
	public function getEnumClass()
	{
		return CourantCategorie::class;
	}

	public function getName(): string
	{
		return 'enumCourantCategorie';
	}
}
