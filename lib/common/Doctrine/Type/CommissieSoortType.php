<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\groepen\enum\CommissieSoort;

class CommissieSoortType extends EnumType {

	public function getEnumClass() {
		return CommissieSoort::class;
	}

	public function getName() {
		return 'enumCommissieSoort';
	}
}
