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
		$url = strtolower('/groepen/' . get_class($model) . '/');
		$this->dataUrl = $url . A::Beheren;
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

		$create = new DataTableKnop('== 0', $this->tableId, $url . A::Aanmaken, 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $url . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, $url . A::Verwijderen, 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['detailSource'] = $groep->getUrl() . 'leden'; // TODO: 2 childrow's A::Rechten;
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
		$this->dataUrl = $groep->getUrl() . A::Rechten;
		$this->hideColumn('action', false);
		$this->searchColumn('action');

		$create = new DataTableKnop('== 0', $this->tableId, $groep->getUrl() . A::Rechten . '/' . A::Aanmaken, 'post popup', 'Geven', 'Rechten uitdelen', 'key_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $groep->getUrl() . A::Rechten . '/' . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig rechten', 'key_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, $groep->getUrl() . A::Rechten . '/' . A::Verwijderen, 'post confirm', 'Terugtrekken', 'Rechten terugtrekken', 'key_delete');
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
		parent::__construct($ac, $groep->getUrl() . A::Rechten . '/' . $action, ucfirst(A::Rechten) . ' voor ');
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
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » ' . $this->getTitel() . '</div>';
	}

	public function getModel() {
		return $this->groepen;
	}

	public function getTitel() {
		return $this->pagina->titel;
	}

}

class GroepView implements View {

	private $groep;
	private $leden;
	private $bb;

	public function __construct(Groep $groep, $tab = null, $bb = false) {
		$this->groep = $groep;
		$this->bb = $bb;
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

	public function getHtml() {
		$html = '<div id="groep-' . $this->groep->id . '" class="bb-groep';
		if ($this->bb) {
			$html .= ' bb-block';
		}
		if ($this->groep->maker_uid == 1025) {
			$html .= ' bb-dies2015';
		}
		$html .= '"><div class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		$html .= CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			$html .= '<div class="clear">&nbsp;</div><a class="groep-omschrijving-tonen" onclick="$(this).next().slideDown();$(this).remove();">Meer lezen »</a><div class="groep-omschrijving">';
			$html .= CsrBB::parse($this->groep->omschrijving);
			$html .= '</div>';
		}
		if (false AND $this->groep instanceof OpvolgbareGroep) {
			/**
			 * TODO
			 */
			$generaties = group_by_distinct('id', $this->groep->getGeneraties());
			$dropdown = new SelectField('generaties', $this->groep->id, null, $generaties);
			$dropdown->onchange = 'window.location.href="/groepen/' . get_class($this->groep) . '/' . $this->groep->id . '"';
			$html .= $dropdown->getHtml();
			$html .= '<a>Opvolger maken</a>';
		}
		$html .= '</div>';
		$html .= $this->leden->getHtml();
		$html .= '<div class="clear">';
		if ($this->groep->maker_uid == 1025) {
			$html .= '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M">';
		}
		$html .= '&nbsp</div></div>';
		return $html;
	}

	public function view() {
		echo '<hr>';
		echo $this->getHtml();
	}

}
