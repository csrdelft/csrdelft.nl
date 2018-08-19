<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\view\formulier\datatable\CellRender;
use CsrDelft\view\formulier\datatable\CellType;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\knop\ConfirmDataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviSaldo::class, '/fiscaat/saldo', 'Saldobeheer');

		$this->addColumn('naam', 'saldo');
		$this->addColumn('lichting', 'saldo');
		$this->addColumn('hidden_saldo', null, null, null, null, null, 'saldo');
		$this->hideColumn('hidden_saldo');
		$this->addColumn('saldo', null, null, CellRender::Bedrag(), 'hidden_saldo', CellType::FormattedNumber());
		$this->setOrder(array('hidden_saldo' => 'asc'));

		$this->searchColumn('naam');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl. '/registreren', 'Registreren', 'Lid registreren', 'toevoegen'));
		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::Any(), $this->dataUrl . '/verwijderen', 'Verwijderen', 'Saldo van lid verwijderen', 'verwijderen'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), $this->dataUrl . '/inleggen', 'Inleggen', 'Saldo van lid ophogen', 'coins_add'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Saldo</span>';
	}
}


