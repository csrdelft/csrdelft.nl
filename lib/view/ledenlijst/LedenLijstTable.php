<?php

namespace CsrDelft\view\ledenlijst;

use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\knop\CollectionDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;
use CsrDelft\view\formulier\ServerSideDataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class LedenLijstTable extends ServerSideDataTable {
	protected $defaultLength = 50;

	public function __construct() {
		parent::__construct(ProfielModel::ORM, '/leden/lijst', 'Ledenlijst');

		$this->settings['select'] = false;

		$this->deleteColumn('details');

		foreach ($this->getColumnDefinition() as $veld) {
			if (!in_array($veld, ['naam', 'adres', 'email', 'mobiel'])) {
				$this->hideColumn($veld);
			}
		}

		$this->searchColumn('naam');
		$this->searchColumn('adres');
		$this->searchColumn('email');

		$this->setOrder(['email' => 'asc']);

		$weergaveKnop = new CollectionDataTableKnop(Multiplicity::Any(), 'Kolommen', 'Kolom weergave');
		$weergaveKnop->addKnop(new DataTableKnop(Multiplicity::Any(), '', '', '', '', 'columnsVisibility'));

		$this->addKnop($weergaveKnop);
	}

	protected function getColumnDefinition() {
		if (LoginModel::mag('P_LEDEN_MOD')) {
			return array_merge(ProfielService::VELDEN, ProfielService::VELDEN_MOD);
		}

		return ProfielService::VELDEN;
	}
}
