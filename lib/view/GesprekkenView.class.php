<?php

/**
 * GesprekkenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekkenView implements View {

	private $gesprek;
	private $gesprekkenTable;
	private $berichtenTable;
	private $berichtForm;

	public function __construct(Gesprek $gesprek = null) {
		$this->gesprek = $gesprek;
		$this->gesprekkenTable = new GesprekkenTable();
		if ($gesprek) {
			GesprekBerichtenModel::instance(); // require_once
			$this->berichtenTable = new GesprekBerichtenTable($gesprek);
			$this->berichtForm = new GesprekBerichtForm($gesprek, $this->berichtenTable->getTableId());
		}
	}

	public function getBreadcrumbs() {
		if ($this->gesprek) {
			$gesprek = $this->berichtenTable->titel;
		} else {
			$gesprek = 'Gesprekken';
		}
		return '<a href="/gesprekken" title="Gesprekken"><span class="fa fa-envelope-o module-icon"></span></a> » <span class="active">' . $gesprek . '</span></div>';
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return 'Gesprekken';
	}

	public function view() {
		echo getMelding();
		echo '<div class="Gesprekken">';
		echo '<h1>' . $this->getTitel() . '</h1>';
		$this->gesprekkenTable->view();
		echo '</div><div class="GesprekBerichten">';
		if ($this->gesprek) {
			$this->berichtenTable->view();
			$this->berichtForm->view();
		}
		echo '</div>';
	}

}

class GesprekkenTable extends DataTable {

	public function __construct() {
		parent::__construct(GesprekkenModel::orm, '/gesprekken/gesprekken');
		$this->settings['tableTools']['aButtons'] = array();

		$this->hideColumn('laatste_update');
		$this->addColumn('deelnemers');

		$create = new DataTableKnop('== 0', $this->dataTableId, '/gesprekken/start', 'post popup', 'Nieuw', 'Nieuw gesprek starten', 'email_add');
		$this->addKnop($create);

		$sluiten = new DataTableKnop('== 1', $this->dataTableId, '/gesprekken/sluiten', 'post confirm', 'Sluiten', 'Gesprek verlaten', 'delete');
		$this->addKnop($sluiten);

		$add = new DataTableKnop('== 1', $this->dataTableId, '/gesprekken/toevoegen', 'post popup', 'Toevoegen', 'Deelnemer toevoegen aan het gesprek', 'user_add');
		$this->addKnop($add);

		$this->javascript .= <<<JS

//TODO: auto update
JS;
	}

}

class GesprekkenResponse extends DataTableResponse {

	public function getJson($gesprek) {
		$array = $gesprek->jsonSerialize();

		$array['details'] = '<a class="lichtgrijs" href="/gesprekken/web/' . $gesprek->gesprek_id . '">';
		if ($gesprek->aantal_nieuw > 0) {
			$array['details'] .= '<span class="badge">' . $gesprek->aantal_nieuw . '</span>';
		} else {
			$array['details'] .= '<span class="fa fa-envelope fa-lg"></span>';
		}
		$array['details'] .= '</a>';
		$array['deelnemers'] = $gesprek->getDeelnemersFormatted();
		$moment = '<span class="lichtgrijs float-right">' . reldate($gesprek->laatste_update) . '</span>';
		$array['laatste_bericht'] = $moment . CsrBB::parse($gesprek->laatste_bericht);

		return parent::getJson($array);
	}

}

class GesprekBerichtenTable extends DataTable {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(GesprekBerichtenModel::orm, '/gesprekken/lees/' . $gesprek->gesprek_id, 'Gesprek met ' . $gesprek->getDeelnemersFormatted());

		$this->hideColumn('details');
		$this->hideColumn('gesprek_id');
		$this->hideColumn('auteur_uid');
		$this->hideColumn('moment');

		$this->javascript .= <<<JS

//TODO: auto update
JS;
	}

}

class BerichtenResponse extends DataTableResponse {

	private $previous;

	public function getJson($bericht) {
		$array = $bericht->jsonSerialize();

		if ($this->previous !== $bericht->auteur_uid) {
			$this->previous = $bericht->auteur_uid;
			$bbcode = '[b]' . ProfielModel::get($bericht->auteur_uid)->getNaam() . '[/b][rn]' . $bericht->inhoud;
		} else {
			$bbcode = $bericht->inhoud;
		}
		$moment = '<span class="lichtgrijs float-right">' . reldate($bericht->moment) . '</span>';
		$array['inhoud'] = $moment . CsrBB::parse($bbcode);

		return parent::getJson($array);
	}

}

class GesprekBerichtForm extends InlineForm {

	public function __construct(Gesprek $gesprek, $tableId = true) {
		$field = new RequiredTextareaField('inhoud', null, null);
		$field->placeholder = 'Bericht';
		parent::__construct(null, '/gesprekken/zeg/' . $gesprek->gesprek_id, $field, false, false);
		$this->dataTableId = $tableId;
		$this->css_classes[] = 'SubmitReset';
	}

}

class GesprekForm extends ModalForm {

	public function __construct() {
		parent::__construct(null, '/gesprekken/start', 'Nieuw gesprek');
		$this->css_classes[] = 'redirect';

		$fields['to'] = new RequiredLidField('to', null, 'Naam of lidnummer');
		$fields['to']->blacklist = array(LoginModel::getUid());
		$fields[] = new RequiredTextareaField('inhoud', null, 'Bericht');
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}

class GesprekDeelnemerToevoegenForm extends ModalForm {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(null, '/gesprekken/toevoegen/' . $gesprek->gesprek_id, 'Deelnemer toevoegen');
		$this->dataTableId = true;

		$fields['to'] = new RequiredLidField('to', null, 'Naam of lidnummer');
		$fields['to']->blacklist = array_keys(group_by_distinct('uid', $gesprek->getDeelnemers()));
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}
