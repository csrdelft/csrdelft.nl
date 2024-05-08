<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\CommissieSoort;

class CommissieSoortType extends EnumType
{
	public function getEnumClass(): string
	{
		return CommissieSoort::class;
	}

	public function getName(): string
	{
		return 'enumCommissieSoort';
	}
}
