<?php


namespace CsrDelft\common\Doctrine\Type;


use CsrDelft\entity\forum\ForumDraadMeldingNiveau;

class ForumDraadMeldingNiveauType extends EnumType {
	protected $name = "enumforumdraadmeldingniveau";
	protected $enumClass = ForumDraadMeldingNiveau::class;
}
