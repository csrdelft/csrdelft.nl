<?php
require_once 'model/entity/groepen/GroepTab.enum.php';
require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenBeheerTable extends DataTable {

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'opvolg_naam');
		$this->dataUrl = groepenUrl . A::Beheren;
		$this->titel = 'Beheer ' . lcfirst(str_replace('Model', '', get_class($model)));
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('website');
		$this->hideColumn('door_uid');
		$this->hideColumn('keuzelijst');
		$this->hideColumn('status_historie');
		$this->searchColumn('naam');
		$this->searchColumn('jaargang');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$create = new DataTableKnop('== 0', $this->tableId, groepenUrl . A::Aanmaken, 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, groepenUrl . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, groepenUrl . A::Verwijderen, 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['detailSource'] = groepenUrl . $groep->id . '/leden'; // TODO: 2 childrow's A::Rechten;
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['door_uid'] = null;

		return parent::getJson($array);
	}

}

class GroepForm extends DataTableForm {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, $action, get_class($groep) . ' ' . A::Wijzigen);
		$fields = $this->generateFields();
	}

}

class GroepRechtenTable extends DataTable {

	public function __construct(AccessModel $model, Groep $groep) {
		parent::__construct($model::orm, 'Rechten voor ' . $groep->naam, 'resource');
		$this->dataUrl = groepenUrl . $groep->id . '/' . A::Rechten;
		$this->hideColumn('action', false);
		$this->searchColumn('action');

		$create = new DataTableKnop('== 0', $this->tableId, groepenUrl . $groep->id . '/' . A::Rechten . '/' . A::Aanmaken, 'post popup', 'Geven', 'Rechten uitdelen', 'key_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Rechten . '/' . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig rechten', 'key_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Rechten . '/' . A::Verwijderen, 'post confirm', 'Terugtrekken', 'Rechten terugtrekken', 'key_delete');
		$this->addKnop($delete);
	}

}

class GroepRechtenData extends DataTableResponse {

	public function getJson($ac) {
		$array = $ac->jsonSerialize();

		$array['resource'] = $ac->resource === '*' ? 'Geerfd' : 'Deze groep';

		return parent::getJson($array);
	}

}

class GroepRechtenForm extends DataTableForm {

	public function __construct(AccessControl $ac, Groep $groep, $action, GroepenModel $model) {
		parent::__construct($ac, groepenUrl . $groep->id . '/' . A::Rechten . '/' . $action, ucfirst(A::Rechten) . ' voor ');
		if ($ac->resource === '*') {
			$this->titel .= 'alle ' . str_replace('Model', '', lcfirst(get_class($model)));
		} else {
			$this->titel .= $groep->naam;
		}

		if ($action === A::Aanmaken) {
			$acties = array();
			foreach (A::getTypeOptions() as $option) {
				$acties[$option] = A::getDescription($option);
			}
			$fields[] = new SelectField('action', $ac->action, 'Actie', $acties);
		} else {
			$fields['a'] = new TextField('action', $ac->action, 'Actie');
			$fields['a']->readonly = true;
		}
		$fields[] = new RechtenField('subject', $ac->subject, 'Rechten');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

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

class GroepLidForm extends InlineForm {

	public function __construct(GroepLid $lid, array $suggesties = array(), $keuzelijst = null) {
		parent::__construct($lid, groepenUrl . $lid->groep_id . '/' . A::Bewerken . '/' . $lid->uid);

		if ($keuzelijst) {
			$this->buttons = true;
			$fields = array();
			$opmerking = explode('&&', $lid->opmerking);
			foreach (explode('&&', $keuzelijst) as $i => $dropdown) {
				$fields[] = new SelectField('opmerking[]', $opmerking[$i], null, explode('|', $dropdown));
			}
			$this->addFields($fields);
		} else {
			$this->field = new TextField('opmerking', $lid->opmerking, 'Functie of opmerking bij lidmaatschap');
			$this->field->suggestions[] = $suggesties;
		}
	}

}

class GroepenView implements View {

	protected $groepen;
	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct(GroepenModel $model, $groepen) {
		$this->groepen = $groepen;
		$naam = str_replace('Model', '', get_class($model));
		$this->pagina = CmsPaginaModel::get($naam);
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
		}
		$this->titel = $this->pagina->titel;
	}

	public function view() {
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->groepen as $groep) {
			$view = new GroepView($groep, GroepTab::Pasfotos);
			$view->view();
		}
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » ' . $this->titel . '</div>';
	}

	public function getModel() {
		
	}

	public function getTitel() {
		
	}

}

class GroepView implements View {

	private $groep;
	private $content;

	public function __construct(Groep $groep, $tab) {
		$this->groep = $groep;
		switch ($tab) {
			case GroepTab::Pasfotos:
				$this->content = new GroepPasfotosView($groep);
				break;
			case GroepTab::Lijst:
				$this->content = new GroepLijstView($groep);
				break;
			case GroepTab::Statistiek:
				$this->content = new GroepStatistiekView($groep);
				break;
			case GroepTab::Emails:
				$this->content = new GroepEmailsView($groep);
				break;
		}
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function view() {
		echo '<hr><div id="groep-' . $this->groep->id . '" class="bb-groep';
		if ($this->groep->door_uid == 1025) {
			echo ' bb-dies2015';
		}
		echo '"><div class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		echo CsrBB::parse($this->groep->samenvatting);
		if (isset($generaties)) {
			$url = groepenUrl;
			echo <<<HTML
<ul class="nobullets generaties">
	<li class="groep-volgende"><a href="{$url}{$generaties['volgende']->id}">{$generaties['volgende']->naam}</a></li>
	<li class="groep-huidige">{$generaties['huidig']->naam}</li>
	<li class="groep-vorige"><a href="{$url}{$generaties['vorige']->id}">{$generaties['vorige']->naam}</a></li>
</ul>
HTML;
		}
		echo '</div>';
		$this->content->view();
		echo '<div class="clear">';
		if ($this->groep->door_uid == 1025) {
			echo '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M">';
		}
		echo '&nbsp;
		</div></div>';
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
		?>
		<div id="groep-leden-<?= $this->groep->id ?>" class="groep-leden">
			<ul class="groep-tabs nobullets">
				<li><a class="btn post noanim <?= ($this instanceof GroepPasfotosView ? 'active' : '' ) ?>" href="<?= groepenUrl . $this->groep->id . '/' . GroepTab::Pasfotos ?>" title="Pasfoto's tonen"><span class="fa fa-user"></span></a></li>
				<li><a class="btn post noanim <?= ($this instanceof GroepLijstView ? 'active' : '' ) ?>" href="<?= groepenUrl . $this->groep->id . '/' . GroepTab::Lijst ?>" title="Lijst en opmerking tonen"><span class="fa fa-align-justify"></span></a></li>
				<li><a class="btn post noanim <?= ($this instanceof GroepStatistiekView ? 'active' : '' ) ?>" href="<?= groepenUrl . $this->groep->id . '/' . GroepTab::Statistiek ?>" title="Statistiek tonen"><span class="fa fa-pie-chart"></span></a></li>
				<li><a class="btn post noanim <?= ($this instanceof GroepEmailsView ? 'active' : '' ) ?>" href="<?= groepenUrl . $this->groep->id . '/' . GroepTab::Emails ?>" title="E-mail's tonen"><span class="fa fa-envelope"></span></a></li>
			</ul>
			<div class="groep-tab-content">
				<?php
			}

		}

		class GroepPasfotosView extends GroepTabView {

			public function view() {
				parent::view();
				foreach ($this->groep->getLeden() as $lid) {
					echo '<div class="pasfoto">' . ProfielModel::getLink($lid->uid, 'pasfoto') . '</div>';
				}
				echo '</div></div>';
			}

		}

		class GroepLijstView extends GroepTabView {

			public function view() {
				parent::view();
				echo '<table class="groep-lijst"><tbody>';
				$suggestions = $this->groep->getSuggesties();
				foreach ($this->groep->getLeden() as $lid) {
					echo '<tr><td>' . ProfielModel::getLink($lid->uid, 'civitas') . '</td>';
					echo '<td>';
					if ($this->groep->mag(A::Bewerken)) {
						$form = new GroepLidForm($lid, $suggestions);
						$form->view();
					} else {
						echo $lid->opmerking;
					}
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
					echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
					if (!is_array($stat)) {
						echo '<tr><td colspan="2">' . $stat . '</td></tr>';
						continue;
					}
					foreach ($stat as $row) {
						echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
					}
				}
				echo '</tbody></table></div></div>';
			}

		}

		class GroepEmailsView extends GroepTabView {

			public function view() {
				parent::view();
				$emails = array();
				foreach ($this->groep->getLeden() as $lid) {
					$profiel = ProfielModel::get($lid->uid);
					if ($profiel AND $profiel->getPrimaryEmail() != '') {
						$emails[] = $profiel->getPrimaryEmail();
					}
				}
				echo '<div class="groep-emails">' . implode(', ', $emails) . '</div></div></div>';
			}

		}
		