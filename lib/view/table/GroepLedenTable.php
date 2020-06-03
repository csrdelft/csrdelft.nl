<?php

namespace CsrDelft\view\table;

use CsrDelft\Component\DataTable\AbstractDataTableType;
use CsrDelft\Component\DataTable\DataTableBuilder;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * GroepLedenTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepLedenTable extends AbstractDataTableType {
	public const OPTION_GROEP = 'groep';
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createDataTable(DataTableBuilder $builder, array $options): void {
		/** @var AbstractGroep $groep */
		$groep = $options[self::OPTION_GROEP];

		$builder->loadFromMetadata($this->entityManager->getClassMetadata($groep->getLidType()));
		$builder->setTitel('Leden van ' . $groep->naam);
		$builder->setDataUrl($groep->getUrl() . '/leden');

		$builder->addColumn('lid', 'opmerking');
		$builder->searchColumn('lid');
		$builder->setColumnTitle('lid', 'Lidnaam');
		$builder->setColumnTitle('door_uid', 'Aangemeld door');

		if ($groep->mag(AccessAction::Beheren)) {
			$builder->addKnop(new DataTableKnop(Multiplicity::Zero(), $groep->getUrl() . '/aanmelden', 'Aanmelden', 'Lid toevoegen', 'user_add'));
			$builder->addRowKnop(new DataTableRowKnop($groep->getUrl() . '/bewerken/:uid', 'Lidmaatschap bewerken', 'user_edit'));
			$builder->addRowKnop(new DataTableRowKnop($groep->getUrl() . '/afmelden/:uid', 'Leden verwijderen', 'user_delete', 'confirm'));
			if ($groep->status == GroepStatus::HT) {
				$builder->addRowKnop(new DataTableRowKnop($groep->getUrl() . '/naar_ot/:uid', 'Naar o.t. groep verplaatsen', 'user_go', 'confirm'));
			}
		}
	}
}
