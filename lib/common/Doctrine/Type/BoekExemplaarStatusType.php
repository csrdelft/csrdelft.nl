<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\bibliotheek\BoekExemplaarStatus;

class BoekExemplaarStatusType extends EnumType {
	protected $name = 'enumboekexemplaarstatus';
	protected $enumClass = BoekExemplaarStatus::class;
}
