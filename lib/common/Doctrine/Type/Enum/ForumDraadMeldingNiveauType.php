<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\forum\ForumDraadMeldingNiveau;

class ForumDraadMeldingNiveauType extends EnumType
{
	public function getEnumClass(): string
	{
		return ForumDraadMeldingNiveau::class;
	}

	public function getName(): string
	{
		return 'enumForumDraadMeldingNiveau';
	}
}
