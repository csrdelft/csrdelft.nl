<?php


namespace CsrDelft\common\Doctrine\Type\Enum;


use CsrDelft\entity\Geslacht;

class GeslachtType extends EnumType {
	public function getEnumClass() {
		return Geslacht::class;
	}

	public function getName() {
		return 'enumGeslacht';
	}
}
