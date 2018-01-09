<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\formulier\datatable\DataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 26/04/2017
 */
class CiviBestellingTable extends DataTable {
	public function __construct($uid = null) {
		$dataUrl = '/fiscaat/bestellingen' . $uid == null ? '' : '/' . $uid;
		parent::__construct(CiviBestelling::class, $dataUrl, "Overzicht voor " . ProfielModel::getNaam($uid, 'volledig'));

		$this->addColumn('inhoud');
		$this->addColumn('totaal', null, null, 'prijs_render', null, 'num-fmt');
		$this->hideColumn('deleted');
		$this->searchColumn('inhoud');
		$this->searchColumn('moment');

		$this->setOrder(array('moment' => 'desc'));
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}
}
