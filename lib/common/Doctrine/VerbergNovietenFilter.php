<?php

namespace CsrDelft\common\Doctrine;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Filter om nieuwe novieten nog niet te tonen.
 * Aan/uit te zetten in config/packages/doctrine.yaml onder doctrine.orm.filters.novieten
 */
class VerbergNovietenFilter extends SQLFilter
{

	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
	{
		if ($targetEntity->getReflectionClass()->name !== Profiel::class) {
			return '';
		} else {
			if (!$this->hasParameter('jaar')) {
				$jaar = date_create_immutable()->format('Y');
			} else {
				$jaar = trim($this->getParameter('jaar'), "'");
			}

			return sprintf("NOT (%s.status = '%s' AND %s.lidjaar = %d)",
				$targetTableAlias,
				LidStatus::Noviet,
				$targetTableAlias,
				intval($jaar)
			);
		}
	}
}
