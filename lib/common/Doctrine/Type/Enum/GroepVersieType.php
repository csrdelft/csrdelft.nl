<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\groepen\enum\GroepVersie;

class GroepVersieType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return GroepVersie::class
	 */
	public function getEnumClass()
	{
		return GroepVersie::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumGroepVersie'
	 */
	public function getName(): string
	{
		return 'enumGroepVersie';
	}
}
