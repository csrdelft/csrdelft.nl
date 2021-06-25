<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\model\entity\groepen\GroepKeuze;

class GroepKeuzeType extends SafeJsonType {

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'groepkeuze';
	}

	protected function getAcceptedTypes() {
		return [GroepKeuze::class];
	}
}
