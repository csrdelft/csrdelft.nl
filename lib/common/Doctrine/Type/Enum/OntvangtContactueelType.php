<?php

namespace CsrDelft\common\Doctrine\Type\Enum;

use CsrDelft\entity\OntvangtContactueel;

class OntvangtContactueelType extends EnumType
{
	/**
	 * @return string
	 *
	 * @psalm-return OntvangtContactueel::class
	 */
	public function getEnumClass()
	{
		return OntvangtContactueel::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'enumOntvangtContactueel'
	 */
	public function getName(): string
	{
		return 'enumOntvangtContactueel';
	}
}
