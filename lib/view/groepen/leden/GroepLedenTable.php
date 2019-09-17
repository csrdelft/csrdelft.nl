<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * GroepLedenTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepLedenTable extends DataTable {

	public function __construct(AbstractGroepLedenModel $model, AbstractGroep $groep) {
		parent::__construct($model::ORM, $groep->getUrl() . 'leden', 'Leden van ' . $groep->naam, 'status');
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		if ($groep->mag(AccessAction::Beheren)) {
			$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $groep->getUrl() . 'aanmelden', 'Aanmelden', 'Lid toevoegen', 'user_add'));
			$this->addRowKnop(new DataTableRowKnop($groep->getUrl() . 'bewerken', 'Lidmaatschap bewerken', 'user_edit'));
			$this->addRowKnop(new DataTableRowKnop($groep->getUrl() . 'afmelden', 'Leden verwijderen', 'user_delete', 'confirm'));
		}
	}

}
