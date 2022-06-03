<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\entity\LidToestemming;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/04/2019
 */
class ToestemmingLijstTable extends DataTable {
	public function __construct($extrakolommen = []) {
		parent::__construct(LidToestemming::class, '/toestemming/lijst', 'Lid toestemmingen');
		$this->addColumn('lid', 'waarde');
		$this->searchColumn('lid');
		$this->addColumn('status', 'waarde');
		$this->deleteColumn('id');
		$this->deleteColumn('waarde');
		$this->deleteColumn('module');
		$this->deleteColumn('instelling_id');
		$this->deleteColumn('instelling');

		foreach ($extrakolommen as $kolom) {
			$this->addColumn($kolom);
			$this->searchColumn($kolom);
		}

		$bronKnop = new CollectionDataTableKnop(Multiplicity::None(), 'Selectie', 'Maak een bron selectie');
        $bronKnop->addKnop(new SourceChangeDataTableKnop('/toestemming/lijst', 'Leden', 'Alle leden en novieten'));
        $bronKnop->addKnop(new SourceChangeDataTableKnop('/toestemming/lijst?filter=oudleden', 'Oudleden', 'Alle oudleden'));
        $bronKnop->addKnop(new SourceChangeDataTableKnop('/toestemming/lijst?filter=ledenoudleden', 'Leden & oudleden', 'Alle leden en oudleden'));
		$bronKnop->addKnop(new SourceChangeDataTableKnop('/toestemming/lijst?filter=iedereen', 'Iedereen', 'Iedereen in de database'));
		$this->addKnop($bronKnop);
	}
}
