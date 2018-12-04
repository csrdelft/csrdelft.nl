<?php
/**
 * GesprekkenTable.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\gesprekken\GesprekkenModel;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class GesprekkenTable extends DataTable {

	public function __construct() {
		parent::__construct(GesprekkenModel::ORM, '/gesprekken/gesprekken');
		$this->defaultLength = -1;
		$this->settings['scrollY'] = '600px';
		$this->settings['scrollCollapse'] = true;
		$this->settings['tableTools']['aButtons'] = array();

		$this->addColumn('deelnemers');
		$this->searchColumn('deelnemers');

		$create = new DataTableKnop(Multiplicity::Zero(), '/gesprekken/start', 'Nieuw', 'Nieuw gesprek starten', 'email_add');
		$this->addKnop($create);

		$sluiten = new DataTableKnop(Multiplicity::One(), '/gesprekken/verlaten', 'Verlaten', 'Gesprek verlaten', 'delete');
		$this->addKnop($sluiten);

		$add = new DataTableKnop(Multiplicity::One(), '/gesprekken/toevoegen', 'Toevoegen', 'Deelnemer toevoegen aan het gesprek', 'user_add');
		$this->addKnop($add);
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$('#{$this->dataTableId}').on('click', 'td:nth-child(2)', function (event) {
	window.location.href = $(this).parent().children('td:first').children('a:first').attr('href');
});
JS;
	}

}
