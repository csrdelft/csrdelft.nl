<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\OntvangtContactueel;

class OntvangtContactueelType extends EnumType {
	protected $name = 'enumontvangtcontactueel';
	protected $enumClass = OntvangtContactueel::class;
}
