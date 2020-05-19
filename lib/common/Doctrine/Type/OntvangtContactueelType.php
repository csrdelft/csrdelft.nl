<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\OntvangtContactueel;

class OntvangtContactueelType extends EnumType {
	public function getEnumClass() {
		return OntvangtContactueel::class;
	}

	public function getName() {
		return 'enumOntvangtContactueel';
	}
}
