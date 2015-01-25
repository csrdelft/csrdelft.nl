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

		$create = new DataTableKnop('== 0', $this->tableId, $groep->getUrl() . A::Aanmelden, 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $groep->getUrl() . A::Bewerken, 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, $groep->getUrl() . A::Afmelden, 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
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
	}

}

class GroepBewerkenForm extends InlineForm {

	public function __construct(GroepLid $lid, Groep $groep, array $suggesties = array(), $keuzelijst = null) {
		parent::__construct($lid, $groep->getUrl() . A::Bewerken . '/' . $lid->uid, true);

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

	public function __construct(GroepLid $lid, Groep $groep, array $suggesties = array(), $keuzelijst = null, $pasfoto = false) {
		parent::__construct($lid, $groep, $suggesties, $keuzelijst);
		$this->action = $groep->getUrl() . A::Aanmelden . '/' . $lid->uid;
		$this->buttons = false;
		$this->css_classes[] = 'float-left';

		$fields[] = $this->field;
		$this->field->hidden = $pasfoto;

		if ($pasfoto) {
			$fields[] = new PasfotoAanmeldenKnop();
		} else {
			$fields[] = new SubmitKnop(null, 'submit', 'Aanmelden', null, null);
		}

		$this->addFields($fields);
	}

	public function getHtml() {
		$html = $this->getFormTag();
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form>';
	}

}

abstract class GroepTabView implements View, FormElement {

	protected $groep;
	protected $javascript;

	public function __construct(Groep $groep) {
		$this->groep = $groep;
		$this->javascript = '';
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getHtml() {
		$html = '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		if ($this->groep->mag(A::Wijzigen)) {
			$html .= '<li class="float-left"><a class="btn" href="' . $this->groep->getUrl() . A::Wijzigen . '" title="Wijzig ' . htmlspecialchars($this->groep->naam) . '"><span class="fa fa-pencil"></span></a></li>';
		}

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Pasfotos . '" title="' . GroepTab::getDescription(GroepTab::Pasfotos) . ' tonen"><span class="fa fa-user"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Lijst . '" title="' . GroepTab::getDescription(GroepTab::Lijst) . ' tonen"><span class="fa fa-align-justify"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Statistiek . '" title="' . GroepTab::getDescription(GroepTab::Statistiek) . ' tonen"><span class="fa fa-pie-chart"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::Emails . '" title="' . GroepTab::getDescription(GroepTab::Emails) . ' tonen"><span class="fa fa-envelope"></span></a></li>';

		if ($this->groep->aantalLeden(GroepStatus::OT) > 0) {
			$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepOTLedenView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::OTleden . '" title="' . GroepTab::getDescription(GroepTab::OTleden) . ' tonen"><span class="fa fa-clock-o"></span></a></li>';
		}

		$html .= '<li class="float-right"><a class="btn vergroot" data-vergroot="#groep-leden-content-' . $this->groep->id . '" title="Vergroot de lijst"><span class="fa fa-expand"></span></a>';

		return $html . '</ul><div id="groep-leden-content-' . $this->groep->id . '" class="groep-tab-content">';
	}

	protected function getCloseHtml() {
		$html = $this->getScriptTag() . '</div><br />';
		if (property_exists($this->groep, 'aanmeld_limiet') AND isset($this->groep->aanmeld_limiet)) {
			$percent = round($this->groep->aantalLeden() * 100 / $this->groep->aanmeld_limiet);
			if (time() > strtotime($this->groep->aanmelden_vanaf) AND time() < strtotime($this->groep->aanmelden_tot)) {
				if ($this->groep->aantalLeden() === $this->groep->aanmeld_limiet) {
					$title = 'Inschrijvingen vol!';
					$color = ' progress-bar-info';
				} else {
					$title = 'Inschrijvingen geopend!';
					$color = ' progress-bar-success';
				}
			} elseif ($this->groep->getLid(LoginModel::getUid()) AND time() < strtotime($this->groep->bewerken_tot)) {
				$title = 'Inschrijvingen gesloten, inschrijving bewerken toegestaan.';
				$color = ' progress-bar-warning';
			} else {
				$title = 'Inschrijvingen gesloten';
				$color = ' progress-bar-info';
			}
			$html .= '<div class="progress" title="' . $title . '"><div class="progress-bar' . $color . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">' . $percent . '%</div></div>';
		}
		return $html . '</div>';
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getType() {
		return get_class($this);
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

	public function getJavascript() {
		return $this->javascript;
	}

}

class GroepPasfotosView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		if (property_exists($this->groep, 'rechten_aanmelden') AND $this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getSuggesties(), $groep->keuzelijst, true);
			$html .= $form->getHtml();
		}
		foreach ($this->groep->getLeden() as $lid) {
			$html .= ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		return $html . parent::getCloseHtml();
	}

}

class GroepLijstView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		$html .= '<table class="groep-lijst"><tbody>';
		$suggesties = $this->groep->getSuggesties();
		if (property_exists($this->groep, 'rechten_aanmelden') AND $this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			$html .= '<tr><td colspan="2">';
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getSuggesties(), $groep->keuzelijst);
			$html .= $form->getHtml();
			$html .= '</td></tr>';
		}
		foreach ($this->groep->getLeden() as $lid) {
			$html .= '<tr><td>' . ProfielModel::getLink($lid->uid, 'civitas') . '</td>';
			$html .= '<td>';
			if ($this->groep->mag(A::Bewerken, $lid->uid)) {
				$form = new GroepBewerkenForm($lid, $this->groep, $suggesties, $this->groep->keuzelijst);
				$html .= $form->getHtml();
			} else {
				$html .= $lid->opmerking;
			}
			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		return $html . parent::getCloseHtml();
	}

}

class GroepStatistiekView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		$html .= '<div class="groep-stats">';
		foreach ($this->groep->getStatistieken() as $titel => $data) {
			$html .= '<h4>' . $titel . '</h4>';
			if (!is_array($data)) {
				$html .= '<div>' . $data . '</div>';
				continue;
			}
			$html .= '<div id="groep-stat-' . $titel . '-' . $this->groep->id . '"></div>';
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

					default:
						if ($titel === 'Lichting') {
							$series[] = array(
								'data' => array(array((int) $row[0], (int) $row[1]))
							);
						} else {
							$series[] = array(
								'label'	 => $row[0],
								'data'	 => $row[1]
							);
						}
				}
			}
			$data = json_encode($series);
			$this->javascript .= <<<JS

var div = $("#groep-stat-{$titel}-{$this->groep->id}");
div.height(div.width());
$.plot(div, {$data}, {
JS;
			switch ($titel) {
				case 'Lichting':
					$this->javascript .= <<<JS

	series: {
		bars: {
			show: true,
			barWidth: 0.5,
			align: "center",
			lineWidth: 0,
			fill: 1
		}
	}
JS;
					break;

				case 'Geslacht':
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
					break;


				case 'Verticale':
				default:
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
			}
			$this->javascript .= <<<JS
});
JS;
		}
		return $html . parent::getCloseHtml();
	}

}

class GroepEmailsView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		$html .= '<div class = "groep-emails">';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . ';
		';
			}
		}
		$html .= '</div>';
		return $html . parent::getCloseHtml();
	}

}

class GroepOTLedenView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		foreach ($this->groep->getLeden(GroepStatus::OT) as $lid) {
			$html .= ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		return $html . parent::getCloseHtml();
	}

}
