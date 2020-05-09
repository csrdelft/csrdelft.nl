<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\groepen\GroepKeuzeSelectie;

class GroepKeuzeSelectieType extends SafeJsonType {
	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'groepkeuzeselectie';
	}

	protected function getAcceptedTypes() {
		return [GroepKeuzeSelectie::class];
	}
}
