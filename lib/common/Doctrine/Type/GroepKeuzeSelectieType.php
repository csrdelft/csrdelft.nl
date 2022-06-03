<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;

class GroepKeuzeSelectieType extends SafeJsonType
{
	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return 'groepkeuzeselectie';
	}

	protected function getAcceptedTypes()
	{
		return [GroepKeuzeSelectie::class];
	}
}
