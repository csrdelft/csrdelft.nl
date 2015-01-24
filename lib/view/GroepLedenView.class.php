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
		$this->dataUrl = groepenUrl . $groep->id . '/leden';
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		$create = new DataTableKnop('== 0', $this->tableId, groepenUrl . $groep->id . '/' . A::Aanmelden, 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Bewerken, 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Afmelden, 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
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
		parent::__construct($lid, groepenUrl . $lid->groep_id . '/' . $action, ucfirst($action));
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

	public function __construct(GroepLid $lid, array $suggesties = array(), $keuzelijst = null) {
		parent::__construct($lid, groepenUrl . $lid->groep_id . '/' . A::Bewerken . '/' . $lid->uid, true);

		if ($keuzelijst) {
			$this->field = new MultiSelectField('opmerking', $lid->opmerking, null);
		} else {
			$this->field = new TextField('opmerking', $lid->opmerking, null);
			$this->field->placeholder = 'Opmerking';
			$this->field->suggestions[] = $suggesties;
		}
	}

}

class GroepAanmeldenForm extends GroepBewerkenForm {

	public function __construct(GroepLid $lid, array $suggesties = array(), $keuzelijst = null) {
		parent::__construct($lid, $suggesties, $keuzelijst);
		$this->action = groepenUrl . $lid->groep_id . '/' . A::Aanmelden . '/' . $lid->uid;
		$this->buttons = false;

		$fields[] = new PasfotoAanmeldenKnop();
		if ($keuzelijst) {
			$fields[] = $this->field;
		}

		$this->addFields($fields);
	}

	public function getHtml() {
		$html = $this->getFormTag();
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form></div>';
	}

}

abstract class GroepTabView implements View {

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

	public function view() {
		echo '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Pasfotos . '" title="Pasfoto\'s tonen"><span class="fa fa-user"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Lijst . '" title="Pasfoto\'s tonen"><span class="fa fa-align-justify"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Statistiek . '" title="Pasfoto\'s tonen"><span class="fa fa-pie-chart"></span></a></li>';

		echo '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'active' : '' ) . '" href="' . groepenUrl . $this->groep->id . '/' . GroepTab::Emails . '" title="Pasfoto\'s tonen"><span class="fa fa-envelope"></span></a></li>';

		echo '</ul><div class="groep-tab-content">';
	}

}

class GroepPasfotosView extends GroepTabView {

	public function view() {
		parent::view();
		foreach ($this->groep->getLeden() as $lid) {
			echo ProfielModel::getLink($lid->uid, 'pasfoto');
		}
		if (property_exists($this->groep, 'rechten_aanmelden') AND $this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep->getSuggesties(), $groep->keuzelijst);
			$form->view();
		}
		echo '</div></div>';
	}

}

class GroepLijstView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<table class="groep-lijst"><tbody>';
		$suggesties = $this->groep->getSuggesties();
		foreach ($this->groep->getLeden() as $lid) {
			echo '<tr><td>' . ProfielModel::getLink($lid->uid, 'civitas') . '</td>';
			echo '<td>';
			if ($this->groep->mag(A::Bewerken, $lid->uid)) {
				$form = new GroepBewerkenForm($lid, $suggesties, $this->groep->keuzelijst);
				$form->view();
			} else {
				echo $lid->opmerking;
			}
			echo '</td></tr>';
		}
		if (property_exists($this->groep, 'rechten_aanmelden') AND $this->groep->mag(A::Aanmelden, LoginModel::getUid())) {
			echo '<tr><td colspan="2">';
			$groep = $this->groep;
			$leden = $groep::leden;
			$lid = $leden::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep->getSuggesties(), $groep->keuzelijst);
			$form->view();
			echo '</td></tr>';
		}
		echo '</tbody></table></div></div>';
	}

}

class GroepStatistiekView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<table class="groep-stats">';
		foreach ($this->groep->getStatistieken() as $title => $stat) {
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead>';
			echo '<tbody>';
			if (!is_array($stat)) {
				echo '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
			echo '</tbody>';
		}
		echo '</table></div></div>';
	}

}

class GroepEmailsView extends GroepTabView {

	public function view() {
		parent::view();
		echo '<div class="groep-emails">';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				echo $profiel->getPrimaryEmail() . '; ';
			}
		}
		echo '</div></div></div>';
	}

}
