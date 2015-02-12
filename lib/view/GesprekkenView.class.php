<?php

/**
 * GesprekkenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekkenView implements View {

	private $gesprekken;
	private $deelnemers;
	private $berichten;

	public function __construct(Gesprek $gesprek = null) {
		$this->gesprekken = new GesprekkenTable(GesprekkenModel::instance());
		//TODO
	}

	public function getBreadcrumbs() {
		return '<a href="/gesprekken" title="Gesprekken"><span class="fa fa-envelope-o module-icon"></span></a>'; //TODO: gesprek met ...
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return 'Gesprekken';
	}

	public function view() {
		$this->gesprekken->view();
	}

}

class GesprekkenTable extends DataTable {

	public function __construct(GesprekkenModel $model, $gesprek_id = null) {
		parent::__construct($model::orm);
		$this->dataUrl = '/gesprekken/gesprekken/' . $gesprek_id;

		$create = new DataTableKnop('== 0', $this->tableId, '/gesprekken/nieuw', 'post popup', 'Nieuw', 'Nieuw gesprek starten', 'add');
		$this->addKnop($create);

		$sluiten = new DataTableKnop('== 1', $this->tableId, '/gesprekken/sluiten', 'post confirm', 'Sluiten', 'Gesprek verlaten', 'delete');
		$this->addKnop($sluiten);
	}

}

class GesprekView extends DataTable {

	public function __construct(GesprekBerichtenModel $model, $gesprek_id = null) {
		parent::__construct($model::orm);
		$this->dataUrl = '/gesprekken/lees/' . $gesprek_id;
	}

}

class GesprekBerichtForm extends DataTableForm {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(null, '/gesprekken/zeg/' . $gesprek->gesprek_id);

		$fields[] = new RequiredTextareaField('inhoud', null, null);
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}

class GesprekForm extends DataTableForm {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(null, '/gesprekken/zeg/' . $gesprek->gesprek_id);

		$fields[] = new RequiredTextareaField('inhoud', null, null);
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}

class GesprekDeelnemerToevoegenForm extends DataTableForm {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(null, '/gesprekken/toevoegen/' . $gesprek->gesprek_id);

		$fields[] = new RequiredLidField('to', null, 'Naam of lidnummer');
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}
