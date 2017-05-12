<?php
/**
 * CiviBestellingOverzichtView.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 26/04/2017
 */

require_once 'model/entity/fiscaat/CiviBestelling.class.php';

class CiviBestellingOverzichtView extends DataTable {
	public function __construct($uid) {
		parent::__construct(CiviBestelling::class, '/fiscaat/bestellingen/' . $uid, "Overzicht voor " . ProfielModel::getNaam($uid, 'volledig'));

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
