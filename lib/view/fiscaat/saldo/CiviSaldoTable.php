<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
class CiviSaldoTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviSaldo::class, '/fiscaat/saldo', 'Saldobeheer');

		$this->addColumn('uid', 'saldo');
		$this->addColumn('naam', 'saldo');
		$this->addColumn('lichting', 'saldo');
		$this->addColumn('hidden_saldo', null, null, null, null, null, 'saldo');
		$this->hideColumn('hidden_saldo');
		$this->addColumn('saldo', null, null, CellRender::Bedrag(), 'hidden_saldo', CellType::FormattedNumber());
		$this->setOrder(array('hidden_saldo' => 'asc'));

		$this->searchColumn('naam');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl. '/registreren', 'Registreren', 'Lid registreren', 'toevoegen'));
		$this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/inleggen', 'Saldo van lid ophogen', 'coins_add'));
		$this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/verwijderen', 'Saldo van lid verwijderen', 'bin', 'confirm'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Saldo</span>';
	}
}


