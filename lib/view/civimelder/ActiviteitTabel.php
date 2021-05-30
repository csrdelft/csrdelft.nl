<?php

namespace CsrDelft\view\civimelder;

use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class ActiviteitTabel extends DataTable {
	public function __construct(Reeks $reeks) {

		parent::__construct(Activiteit::class, '/civimelder/beheer/activiteiten/' . $reeks->getId(), $reeks->getNaam() . ' activiteiten', null, false);

		$this->addColumn('start');
		$this->addColumn('einde');
		$this->addColumn('bezetting');

		$this->setOrder(['start' => 'asc']);
		$this->searchColumn('start');
		$this->searchColumn('einde');

		$weergave = new CollectionDataTableKnop(Multiplicity::None(), 'Weergave', 'Weergave van tabel', '');
		$weergave->addKnop(new SourceChangeDataTableKnop($this->dataUrl, 'Toekomst', 'Toekomst weergeven', 'time_go'));
		$weergave->addKnop(new SourceChangeDataTableKnop($this->dataUrl . '?filter=alles', 'Alles', 'Alles weergeven', 'time'));
		$this->addKnop($weergave);

		if ($reeks->magActiviteitenBeheren()) {
			$this->addKnop(new DataTableKnop(Multiplicity::None(), '/civimelder/beheer/activiteiten/nieuw/' . $reeks->getId(), 'Nieuw', 'Nieuwe activiteit aanmaken', 'add'));
			$this->addKnop(new DataTableKnop(Multiplicity::One(), '/civimelder/beheer/activiteiten/bewerken', 'Bewerken', 'Deze activiteit bewerken', 'pencil'));
			$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/civimelder/beheer/activiteiten/verwijderen', 'Verwijderen', 'Activiteit verwijderen', 'cross'));
		}
	}
}
