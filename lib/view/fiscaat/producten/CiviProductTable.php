<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 04/04/2017
 */
class CiviProductTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviProduct::class, '/fiscaat/producten', 'Productenbeheer');

		$this->selectEnabled = false;

		$this->addColumn('id');
		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
		$this->addColumn('beheer', 'prijs', null, CellRender::Check());
		$this->addColumn('categorie', 'prijs');
		$this->hideColumn('prioriteit');
		$this->deleteColumn('categorie_id');

		$this->searchColumn('beschrijving');
		$this->searchColumn('categorie');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . '/bewerken', 'Nieuw', 'Nieuw product toevoegen', 'add'));
		$this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/bewerken', 'Product bewerken', 'pencil'));
		$this->addRowKnop(new DataTableRowKnop( $this->dataUrl . '/verwijderen', 'Product verwijderen', 'bin', 'confirm'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Producten</span>';
	}
}
