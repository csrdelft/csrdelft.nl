<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\CommissieSoort;

class CommissieSoortType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return CommissieSoort::class
	 */
	public function getEnumClass()
	{
		return CommissieSoort::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumCommissieSoort'
	 */
	public function getName(): string
	{
		return 'enumCommissieSoort';
	}
}
