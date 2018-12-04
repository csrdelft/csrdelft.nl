<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class CiviProductTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviProduct::class, '/fiscaat/producten', 'Productenbeheer');

		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
		$this->addColumn('beheer', 'prijs', null, CellRender::Check());
		$this->addColumn('categorie', 'prijs');
		$this->hideColumn('prioriteit');
		$this->deleteColumn('categorie_id');

		$this->searchColumn('beschrijving');
		$this->searchColumn('categorie');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . '/toevoegen', 'Nieuw', 'Nieuw product toevoegen', 'add'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), $this->dataUrl . '/bewerken', 'Bewerken', 'Product bewerken', 'pencil'));
		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), $this->dataUrl . '/verwijderen', 'Verwijderen', 'Product verwijderen', 'cross'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Producten</span>';
	}
}
