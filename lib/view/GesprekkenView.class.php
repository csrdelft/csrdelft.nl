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

	public function __construct(Gesprek $gesprek = null, $filter = null) {
		if ($gesprek) {
			$this->gesprek = $gesprek;
			GesprekBerichtenModel::instance(); // require_once
			$this->berichtenTable = new GesprekBerichtenTable($gesprek);
			$this->berichtForm = new GesprekBerichtForm($gesprek, $this->berichtenTable->getDataTableId());
		} else {
			$this->gesprekkenTable = new GesprekkenTable();
			$this->gesprekkenTable->filter = $filter;
		}
	}

	public function getBreadcrumbs() {
		if ($this->gesprek) {
			$gesprek = $this->berichtenTable->getTitel();
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
		if ($this->gesprek) {
			echo '<div class="GesprekBerichten">';
			$this->berichtenTable->view();
			$this->berichtForm->view();
		} else {
			echo '<div class="Gesprekken">';
			echo '<h1>' . $this->getTitel() . '</h1>';
			$this->gesprekkenTable->view();
		}
		echo '</div>';
	}

}

class GesprekkenTable extends DataTable {

	public function __construct() {
		parent::__construct(GesprekkenModel::ORM, '/gesprekken/gesprekken');
		$this->defaultLength = -1;
		$this->settings['scrollY'] = '600px';
		$this->settings['scrollCollapse'] = true;
		$this->settings['tableTools']['aButtons'] = array();

		$this->addColumn('deelnemers');
		$this->searchColumn('deelnemers');

		$create = new DataTableKnop('== 0', $this->dataTableId, '/gesprekken/start', 'post popup', 'Nieuw', 'Nieuw gesprek starten', 'email_add');
		$this->addKnop($create);

		$sluiten = new DataTableKnop('== 1', $this->dataTableId, '/gesprekken/verlaten', 'post confirm', 'Verlaten', 'Gesprek verlaten', 'delete');
		$this->addKnop($sluiten);

		$add = new DataTableKnop('== 1', $this->dataTableId, '/gesprekken/toevoegen', 'post popup', 'Toevoegen', 'Deelnemer toevoegen aan het gesprek', 'user_add');
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

		$laatste_bericht = GesprekBerichtenModel::instance()->find('gesprek_id = ?', array($gesprek->gesprek_id), null, 'bericht_id DESC', 1)->fetch();
		if ($laatste_bericht) {
			$array['laatste_update'] = $laatste_bericht->getFormatted(false, 30);
		}

		return parent::getJson($array);
	}

}

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
		return parent::getJavascript() .<<<JS

$(document).ready(function (event) {
	$('textarea[name="inhoud"]').focus();
});
JS;
	}

}

class BerichtenResponse extends DataTableResponse {

	public function getJson($bericht) {
		$array = $bericht->jsonSerialize();

		$previous = GesprekBerichtenModel::instance()->find('gesprek_id = ? AND bericht_id < ?', array($bericht->gesprek_id, $bericht->bericht_id), null, 'bericht_id DESC', 1)->fetch();
		$array['inhoud'] = $bericht->getFormatted($previous);

		return parent::getJson($array);
	}

}

class GesprekBerichtForm extends InlineForm {

	public function __construct(Gesprek $gesprek, $dataTableId = true) {
		$field = new RequiredTextareaField('inhoud', null, null);
		$field->placeholder = 'Bericht';
		parent::__construct(null, '/gesprekken/zeg/' . $gesprek->gesprek_id, $field, false, false, $dataTableId);
		$this->css_classes[] = 'SubmitReset';
		$this->css_classes[] = 'noanim';
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
		parent::__construct(null, '/gesprekken/toevoegen/' . $gesprek->gesprek_id, 'Deelnemer toevoegen', true);

		$fields['to'] = new RequiredLidField('to', null, 'Naam of lidnummer');
		$fields['to']->blacklist = array_keys(group_by_distinct('uid', $gesprek->getDeelnemers()));
		$fields[] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}

}
