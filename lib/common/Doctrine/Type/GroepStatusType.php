<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\groepen\GroepStatus;

class GroepStatusType extends EnumType {
	protected $enumClass = GroepStatus::class;
	protected $name = "enumgroepstatus";

}
