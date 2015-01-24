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
		parent::__construct($lid, $action, ucfirst($action));
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

		if ($pasfoto) {
			$fields[] = new PasfotoAanmeldenKnop();
		} else {
			$fields[] = $this->field;
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

	public function __construct(Groep $groep) {
		$this->groep = $groep;
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

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepOTLedenView ? 'active' : '' ) . '" href="' . $this->groep->getUrl() . GroepTab::OTleden . '" title="' . GroepTab::getDescription(GroepTab::OTleden) . ' tonen"><span class="fa fa-clock-o"></span></a></li>';

		return $html . '</ul><div class="groep-tab-content">';
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getType() {
		return get_class($this);
	}

	public function getJavascript() {
		return '';
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
		return $html . '</div></div>';
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
		return $html . '</tbody></table></div></div>';
	}

}

class GroepStatistiekView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		$html .= '<table class="groep-stats">';
		foreach ($this->groep->getStatistieken() as $title => $stat) {
			$html .= '<thead><tr><th colspan="2">' . $title . '</th></tr></thead>';
			$html .= '<tbody>';
			if (!is_array($stat)) {
				$html .= '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				$html .= '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
			$html .= '</tbody>';
		}
		return $html . '</table></div></div>';
	}

}

class GroepEmailsView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		$html .= '<div class="groep-emails">';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . '; ';
			}
		}
		return $html . '</div></div></div>';
	}

}

class GroepOTLedenView extends GroepTabView {

	public function getHtml() {
		$html = parent::getHtml();
		foreach ($this->groep->getLeden(GroepStatus::OT) as $lid) {
			$html .= ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		return $html . '</div></div>';
	}

}
