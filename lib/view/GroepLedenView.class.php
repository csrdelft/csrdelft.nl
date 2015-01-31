<?php

/**
 * GroepLedenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepLedenTable extends DataTable {

	public function __construct(GroepLedenModel $model, Groep $groep) {
		parent::__construct($model::orm, 'Leden van ' . $groep->naam, 'status');
		$this->dataUrl = $groep->getUrl() . 'leden';
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		$create = new DataTableKnop('== 0', $this->tableId, $groep->getUrl() . 'aanmelden', 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $groep->getUrl() . 'bewerken', 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, $groep->getUrl() . 'afmelden', 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
		$this->addKnop($delete);
	}

}

class GroepLedenData extends DataTableResponse {

	public function getJson($lid) {
		$array = $lid->jsonSerialize();

		$array['uid'] = ProfielModel::getLink($array['uid'], 'civitas');
		$array['door_uid'] = ProfielModel::getLink($array['door_uid'], 'civitas');

		return parent::getJson($array);
	}

}

class GroepLidBeheerForm extends DataTableForm {

	public function __construct(GroepLid $lid, $action, array $blacklist = null) {
		parent::__construct($lid, $action, 'Aanmelding bewerken');

		$fields = $this->generateFields();

		if ($blacklist !== null) {
			$fields['uid']->blacklist = $blacklist;
			$fields['uid']->required = true;
			$fields['uid']->readonly = false;
		}
		$fields['uid']->hidden = false;
		$fields['door_uid']->required = true;
		$fields['door_uid']->readonly = true;
		$fields['door_uid']->hidden = true;

		$this->addFields(array(new FormDefaultKnoppen()));
	}

}

class GroepBewerkenForm extends InlineForm {

	public function __construct(GroepLid $lid, Groep $groep, array $suggesties = array(), $keuzelijst = null) {
		parent::__construct($lid, $groep->getUrl() . 'bewerken/' . $lid->uid, true);

		if ($keuzelijst) {
			$this->field = new MultiSelectField('opmerking', $lid->opmerking, null, $keuzelijst);
		} else {
			$this->field = new TextField('opmerking', $lid->opmerking, null);
			$this->field->placeholder = 'Opmerking';
			$this->field->suggestions[] = $suggesties;
		}
	}

}

class GroepAanmeldenForm extends GroepBewerkenForm {

	private $aanmeldknop;

	public function __construct(GroepLid $lid, Groep $groep, array $suggesties = array(), $keuzelijst = null, $pasfoto = false) {
		parent::__construct($lid, $groep, $suggesties, $keuzelijst);
		$this->action = $groep->getUrl() . 'aanmelden/' . $lid->uid;
		$this->buttons = false;
		$this->css_classes[] = 'float-left';

		$fields[] = $this->field;
		$this->field->hidden = $pasfoto;

		if ($pasfoto) {
			$this->aanmeldknop = new PasfotoAanmeldenKnop();
		} else {
			$this->aanmeldknop = new SubmitKnop(null, 'submit', 'Aanmelden', null, null);
		}

		$this->addFields($fields);
	}

	public function getHtml() {
		$html = $this->getFormTag();
		$html .= $this->field->getHtml();
		$html .= $this->aanmeldknop->getHtml();
		$html .= $this->getScriptTag();
		return $html . '</form>';
	}

}

class GroepOmschrijvingView implements View, FormElement {

	protected $groep;
	protected $javascript;

	public function __construct(Groep $groep) {
		$this->groep = $groep;
		$this->javascript = '';
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getType() {
		return get_class($this);
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getJavascript() {
		return $this->javascript;
	}

	public function getHtml() {
		$this->javascript .= <<<JS

$('#groep-omschrijving-{$this->groep->id}').hide().slideDown(600);
JS;
		echo '<div id="groep-omschrijving-' . $this->groep->id . '">';
		echo CsrBB::parse($this->groep->omschrijving);
		echo $this->getScriptTag();
		echo '</div>';
	}

	public function view() {
		echo $this->getHtml();
	}

	protected function getScriptTag() {
		return <<<JS
<script type="text/javascript">
$(document).ready(function () {
	{$this->getJavascript()}
});
</script>
JS;
	}

}

abstract class GroepTabView extends GroepOmschrijvingView {

	protected abstract function getTabContent();

	public function getHtml() {
		$html = '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		if ($this->groep->mag(A::Wijzigen)) {
			$html .= '<li class="float-left"><a class="btn" href="' . $this->groep->getUrl() . 'wijzigen' . '" title="Wijzig ' . htmlspecialchars($this->groep->naam) . '"><span class="fa fa-pencil"></span></a></li>';
		}

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Pasfotos . '" title="' . GroepTab::getDescription(GroepTab::Pasfotos) . ' tonen"><span class="fa fa-user"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Lijst . '" title="' . GroepTab::getDescription(GroepTab::Lijst) . ' tonen"><span class="fa fa-align-justify"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Statistiek . '" title="' . GroepTab::getDescription(GroepTab::Statistiek) . ' tonen"><span class="fa fa-pie-chart"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Emails . '" title="' . GroepTab::getDescription(GroepTab::Emails) . ' tonen"><span class="fa fa-envelope"></span></a></li>';

		$onclick = "$('#groep-" . $this->groep->id . "').toggleClass('leden-uitgeklapt');";
		$html .= '<li class="float-right"><a class="btn vergroot" id="groep-vergroot-' . $this->groep->id . '" data-vergroot="#groep-leden-content-' . $this->groep->id . '" title="Uitklappen" onclick="' . $onclick . '"><span class="fa fa-expand"></span></a>';

		$html .= '</ul><div id="groep-leden-content-' . $this->groep->id . '" class="groep-tab-content ' . $this->getType() . '">';

		$html .= $this->getTabContent();

		$this->javascript .= <<<JS

var tabContent = $('#groep-leden-content-{$this->groep->id}');
var availableHeight = tabContent.parent().parent().height() - tabContent.parent().height();
if ($('#groep-{$this->groep->id}').hasClass('leden-uitgeklapt')) {
	tabContent.height(tabContent.prop('scrollHeight') + 1);
	var knop = $('#groep-vergroot-{$this->groep->id}');
	knop.attr('title', 'Inklappen');
	knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
	knop.attr('data-vergroot-oud', availableHeight);
}
else {
	tabContent.height(availableHeight);
}
JS;
		$html .= $this->getScriptTag();

		$html .= '</div>';

		if (property_exists($this->groep, 'aanmeld_limiet') AND isset($this->groep->aanmeld_limiet)) {
			// Progress bar
			$percent = round($this->groep->aantalLeden() * 100 / $this->groep->aanmeld_limiet);
			// Aanmelden mogelijk?
			if (time() > strtotime($this->groep->aanmelden_vanaf) AND time() < strtotime($this->groep->aanmelden_tot)) {
				$verschil = $this->groep->aanmeld_limiet - $this->groep->aantalLeden();
				if ($verschil === 0) {
					$title = 'Inschrijvingen vol!';
					$color = ' progress-bar-info';
				} else {
					$title = 'Inschrijvingen geopend! Nog ' . $verschil . ' plek' . ($verschil === 1 ? '' : 'ken') . ' vrij.';
					$color = ' progress-bar-success';
				}
			}
			// Bewerken mogelijk?
			elseif ($this->groep->getLid(LoginModel::getUid()) AND time() < strtotime($this->groep->bewerken_tot)) {
				$title = 'Inschrijvingen gesloten! Inschrijving bewerken is nog wel toegestaan.';
				$color = ' progress-bar-warning';
			} else {
				$title = 'Inschrijvingen gesloten!';
				$color = ' progress-bar-info';
			}
			$html .= '<br /><div class="progress" title="' . $title . '"><div class="progress-bar' . $color . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">' . $percent . '%</div></div>';
		}
		return $html . '</div>';
	}

}

class GroepPasfotosView extends GroepTabView {

	protected function getTabContent() {
		$html = '';
		if ($this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst, true);
			$form->css_classes[] = 'pasfotos';
			$html .= $form->getHtml();
		}
		foreach ($this->groep->getLeden() as $lid) {
			$html .= ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		return $html;
	}

}

class GroepLijstView extends GroepTabView {

	public function getTabContent() {
		$html = '<table class="groep-lijst"><tbody>';
		$suggesties = $this->groep->getOpmerkingSuggesties();
		if ($this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			$html .= '<tr><td colspan="2">';
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst);
			$html .= $form->getHtml();
			$html .= '</td></tr>';
		}
		$leden = array();
		foreach ($this->groep->getLeden() as $lid) {
			$achternaam = ProfielModel::instance()->findSparse(array('achternaam'), 'uid = ?', array($lid->uid), null, null, 1)->fetchColumn(1);
			if ($achternaam) {
				$leden[$achternaam] = $lid;
			}
		}
		ksort($leden);
		foreach ($leden as $lid) {
			$html .= '<tr><td>';
			if ($this->groep->mag(A::Afmelden, $lid->uid)) {
				$html .= '<a href="' . $this->groep->getUrl() . 'afmelden" class="post confirm float-left" title="Afmelden"><img src="/plaetjes/famfamfam/bullet_delete.png" class="icon" width="16" height="16"></a>';
			}
			$html .= ProfielModel::getLink($lid->uid, 'civitas');
			$html .= '</td><td>';
			if ($this->groep->mag(A::Bewerken, $lid->uid)) {
				$form = new GroepBewerkenForm($lid, $this->groep, $suggesties, $this->groep->keuzelijst);
				$html .= $form->getHtml();
			} else {
				$html .= $lid->opmerking;
			}
			$html .= '</td></tr>';
		}
		return $html . '</tbody></table>';
	}

}

class GroepStatistiekView extends GroepTabView {

	private function verticale($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array(
				'label'	 => $row[0],
				'data'	 => $row[1]
			);
		}
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			label: {
				show: true,
				radius: 2/3,
				formatter: function(label, series) {
					return '<div class="pie-chart-label">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
				},
				threshold: 0.1
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
	}

	private function geslacht($data) {
		$series = array();
		foreach ($data as $row) {
			switch ($row[0]) {

				case 'm':
					$series[] = array(
						'label'	 => '',
						'data'	 => $row[1],
						'color'	 => '#AFD8F8'
					);
					break;

				case 'v':
					$series[] = array(
						'label'	 => '',
						'data'	 => $row[1],
						'color'	 => '#FFCBDB'
					);
					break;
			}
		}
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			innerRadius: .5,
			label: {
				show: false
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
	}

	private function lichting($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array('data' => array(array((int) $row[0], (int) $row[1])));
		}
		$this->javascript .= <<<JS

	series: {
		bars: {
			show: true,
			barWidth: 0.5,
			align: "center",
			lineWidth: 0,
			fill: 1
		}
	},
	xaxis: {
		tickDecimals: 0
	},
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

	private function tijd($data) {
		$series = array();
		$totaal = 0;
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[0][] = array($tijd, $totaal);
		}
		$this->javascript .= <<<JS

	xaxes: [{
		mode: "time"
	}],
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

	public function getTabContent() {
		$html = '';

		foreach ($this->groep->getStatistieken() as $titel => $data) {
			$html .= '<h4>' . $titel . '</h4>';
			if (!is_array($data)) {
				$html .= $data;
				continue;
			}
			$html .= '<div id="groep-stat-' . $titel . '-' . $this->groep->id . '" class="groep-stat"></div>';
			$this->javascript .= <<<JS

var div = $("#groep-stat-{$titel}-{$this->groep->id}");
div.height(div.width());
$.plot(div, data{$titel}{$this->groep->id}, {
JS;
			switch ($titel) {

				case 'Verticale': $series = $this->verticale($data);
					break;

				case 'Geslacht': $series = $this->geslacht($data);
					break;

				case 'Lichting': $series = $this->lichting($data);
					break;

				case 'Tijd': $series = $this->tijd($data);
					break;
			}
			// prepend data
			$data = json_encode($series);
			$this->javascript = <<<JS

var data{$titel}{$this->groep->id} = {$data};
{$this->javascript}
});
JS;
		}
		return $html;
	}

}

class GroepEmailsView extends GroepTabView {

	public function getTabContent() {
		$html = '';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . '; ';
			}
		}
		return $html;
	}

}
