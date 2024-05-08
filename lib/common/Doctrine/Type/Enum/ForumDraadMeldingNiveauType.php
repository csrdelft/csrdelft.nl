<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\forum\ForumDraadMeldingNiveau;

class ForumDraadMeldingNiveauType extends EnumType
{
	public function getEnumClass()
	{
		return ForumDraadMeldingNiveau::class;
	}

	public function getName()
	{
		return 'enumForumDraadMeldingNiveau';
	}
}
