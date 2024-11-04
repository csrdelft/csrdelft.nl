<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\Geslacht;

class GeslachtType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return Geslacht::class
	 */
	public function getEnumClass()
	{
		return Geslacht::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumGeslacht'
	 */
	public function getName(): string
	{
		return 'enumGeslacht';
	}
}
