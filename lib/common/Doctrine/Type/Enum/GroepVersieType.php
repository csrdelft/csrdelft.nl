<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\GroepVersie;

class GroepVersieType extends EnumType
{
	public function getEnumClass(): string
	{
		return GroepVersie::class;
	}

	public function getName(): string
	{
		return 'enumGroepVersie';
	}
}
