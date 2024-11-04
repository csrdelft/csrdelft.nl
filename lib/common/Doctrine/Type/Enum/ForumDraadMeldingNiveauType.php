<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\forum\ForumDraadMeldingNiveau;

class ForumDraadMeldingNiveauType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return ForumDraadMeldingNiveau::class
	 */
	public function getEnumClass()
	{
		return ForumDraadMeldingNiveau::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumForumDraadMeldingNiveau'
	 */
	public function getName(): string
	{
		return 'enumForumDraadMeldingNiveau';
	}
}
