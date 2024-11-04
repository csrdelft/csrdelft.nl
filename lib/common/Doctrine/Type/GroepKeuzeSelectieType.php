<?php

namespace CsrDelft\common\Doctrine\Type;

use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;

class GroepKeuzeSelectieType extends SafeJsonType
{
	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return 'groepkeuzeselectie';
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return list{GroepKeuzeSelectie::class}
	 */
	protected function getAcceptedTypes()
	{
		return [GroepKeuzeSelectie::class];
	}
}
