<?php

namespace CsrDelft\sview\fiscaat\pin;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\view\formulier\datatable\DataTable;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinBestellingTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviBestellingModel::ORM, '/fiscaat/pin/bestelling', 'Overzicht van pin bestellingen');
	}
}
