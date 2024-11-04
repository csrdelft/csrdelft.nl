<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\courant\CourantCategorie;

class CourantCategorieType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return CourantCategorie::class
	 */
	public function getEnumClass()
	{
		return CourantCategorie::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumCourantCategorie'
	 */
	public function getName(): string
	{
		return 'enumCourantCategorie';
	}
}
