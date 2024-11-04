<?php

namespace CsrDelft\common\Doctrine\Type;

use CsrDelft\model\entity\groepen\GroepKeuze;

class GroepKeuzeType extends SafeJsonType
{
	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return 'groepkeuze';
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return list{GroepKeuze::class}
	 */
	protected function getAcceptedTypes()
	{
		return [GroepKeuze::class];
	}
}
