<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\groepen\enum\GroepVersie;

class GroepVersieType extends EnumType {
	public function getEnumClass() {
		return GroepVersie::class;
	}

	public function getName() {
		return 'enumGroepVersie';
	}
}
