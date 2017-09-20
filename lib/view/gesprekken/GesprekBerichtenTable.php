<?php
/**
 * GesprekBerichtenTable.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\view\formulier\datatable\DataTable;

class GesprekBerichtenTable extends DataTable {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(GesprekBerichtenModel::ORM, '/gesprekken/lees/' . $gesprek->gesprek_id, 'Gesprek met ' . $gesprek->getDeelnemersFormatted());
		$this->defaultLength = -1;
		$this->settings['scrollY'] = '600px';
		$this->settings['scrollCollapse'] = true;
		$this->settings['tableTools']['aButtons'] = array('select_all', 'select_none', 'copy', 'xls', 'pdf');

		$this->hideColumn('details');
		$this->hideColumn('gesprek_id');
		$this->hideColumn('auteur_uid');
		$this->hideColumn('moment');
		$this->searchColumn('inhoud');
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$(document).ready(function (event) {
	$('textarea[name="inhoud"]').focus();
});
JS;
	}

}