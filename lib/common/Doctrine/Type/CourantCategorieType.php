<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\courant\CourantCategorie;

class CourantCategorieType extends EnumType {
	protected $name = 'enumcourantcategorie';
	protected $enumClass = CourantCategorie::class;
}
