<?php

namespace CsrDelft\view\aanmelder;

use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\PopupDataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class AanmeldActiviteitTabel extends DataTable
{
	public function __construct(Reeks $reeks)
	{

		parent::__construct(AanmeldActiviteit::class, '/aanmelder/beheer/activiteiten/' . $reeks->getId(), $reeks->getNaam() . ' activiteiten', null, false);

		$this->addColumn('id');
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

		$this->addKnop(new PopupDataTableKnop(Multiplicity::One(), '/aanmelder/beheer/lijst/:id', 'Lijst', 'Lijst weergeven', 'list'));
		if ($reeks->magActiviteitenBeheren()) {
			$this->addKnop(new DataTableKnop(Multiplicity::None(), '/aanmelder/beheer/activiteiten/nieuw/' . $reeks->getId(), 'Nieuw', 'Nieuwe activiteit aanmaken', 'add'));
			$this->addKnop(new DataTableKnop(Multiplicity::One(), '/aanmelder/beheer/activiteiten/bewerken', 'Bewerken', 'Deze activiteit bewerken', 'pencil'));
			$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/aanmelder/beheer/activiteiten/verwijderen', 'Verwijderen', 'Activiteit verwijderen', 'cross'));
		}
	}
}
