<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchTable extends DataTable {
	public function __construct() {
		parent::__construct(PinTransactieMatchModel::ORM, '/fiscaat/pin/overzicht?filter=metFout', 'Overzicht van pintransacties matches');

		$weergave = new DataTableKnop('', $this->dataTableId, '', '', 'Weergave', 'Weergave van de tabel', 'cart', 'collection');
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/pin/overzicht?filter=metFout', '', 'Met fouten', 'Fouten weergeven', 'cart_error', 'sourceChange'));
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/pin/overzicht?filter=alles', '', 'Alles', 'Alles weergeven', 'cart', 'sourceChange'));
		$this->addKnop($weergave);

		$this->addKnop(new DataTableKnop('== 1',  $this->dataTableId, '/fiscaat/pin/verwerk', '',  'Verwerk', 'Dit probleem verwerken', 'cart_edit'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/pin/ontkoppel', '', 'Ontkoppel', 'Ontkoppel bestelling en transactie', 'arrow_divide', 'confirm'));
		$this->addKnop(new DataTableKnop('== 2', $this->dataTableId, '/fiscaat/pin/koppel', '', 'Koppel', 'Koppel een bestelling en transactie', 'arrow_join'));

		$this->addColumn('moment');
		$this->addColumn('transactie');
		$this->addColumn('bestelling');

		$this->hideColumn('transactie_id');
		$this->hideColumn('bestelling_id');
	}
}
