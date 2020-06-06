<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\groepen\enum\GroepStatus;

class GroepStatusType extends EnumType {
	public function getEnumClass() {
		return GroepStatus::class;
	}

	public function getName() {
		return 'enumGroepStatus';
	}
}
