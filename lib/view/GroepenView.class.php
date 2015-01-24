<?php

require_once 'model/entity/groepen/GroepTab.enum.php';
require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';
require_once 'view/GroepLedenView.class.php';

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
		$this->hideColumn('maker_uid');
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
		$array['maker_uid'] = null;

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
	private $leden;

	public function __construct(Groep $groep, $tab = null) {
		$this->groep = $groep;
		switch ($tab) {

			case GroepTab::Pasfotos:
				$this->leden = new GroepPasfotosView($groep);
				break;

			case GroepTab::Lijst:
				$this->leden = new GroepLijstView($groep);
				break;

			case GroepTab::Statistiek:
				$this->leden = new GroepStatistiekView($groep);
				break;

			case GroepTab::Emails:
				$this->leden = new GroepEmailsView($groep);
				break;

			default:
				if ($groep->keuzelijst) {
					$this->leden = new GroepLijstView($groep);
				} else {
					$this->leden = new GroepPasfotosView($groep);
				}
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
		if ($this->groep->maker_uid == 1025) {
			echo ' bb-dies2015';
		}
		echo '"><div class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		echo CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			echo '<div class="groep-omschrijving-tonen" onclick="$(this).next().slideDown();">Verder lezen...</div><div class="groep-omschrijving">';
			echo CsrBB::parse($this->groep->omschrijving);
			echo '</div>';
		}
		if (false AND $this->groep instanceof OpvolgbareGroep) {
			/**
			 * TODO
			 */
			$generaties = group_by_distinct('id', $this->groep->getGeneraties());
			$dropdown = new SelectField('generaties', $this->groep->id, null, $generaties);
			$dropdown->onchange = 'window.location.href="/groepen/' . get_class($this->groep) . '/' . $this->groep->id . '"';
			$dropdown->view();
			echo '<a>Opvolger maken</a>';
		}
		echo '</div>';
		$this->leden->view();
		echo '<div class="clear">';
		if ($this->groep->maker_uid == 1025) {
			echo '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M">';
		}
		echo '&nbsp;
		</div></div>';
	}

}
