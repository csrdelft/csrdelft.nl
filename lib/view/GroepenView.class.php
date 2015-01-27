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

	protected $url;
	protected $naam;

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'opvolg_naam');

		$this->url = $model->getUrl();
		$this->dataUrl = $this->url . A::Beheren;

		$this->naam = $model->getNaam();
		$this->titel = 'Beheer ' . lcfirst($this->naam);

		$this->hideColumn('id', false);
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('website');
		$this->hideColumn('maker_uid');
		$this->hideColumn('keuzelijst');
		$this->hideColumn('status_historie');
		$this->hideColumn('rechten_aanmelden');
		$this->hideColumn('rechten_beheren');
		$this->searchColumn('naam');
		$this->searchColumn('jaargang');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$create = new DataTableKnop('== 0', $this->tableId, $this->url . A::Aanmaken, 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $this->url . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		$convert = new DataTableKnop('== 1', $this->tableId, $this->url . 'converteren', 'post popup', 'Converteren', 'Converteer groep', 'lightning');
		$this->addKnop($convert);

		$delete = new DataTableKnop('>= 1', $this->tableId, $this->url . A::Verwijderen, 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <a href="' . $this->url . '">' . $this->naam . '</a> » <span class="active">Beheren</span>';
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['detailSource'] = $groep->getUrl() . 'leden'; // TODO: 2 childrow's A::Rechten;
		$array['naam'] = '<span title="' . $groep->naam . (empty($groep->samenvatting) ? '' : '&#13;&#13;') . substr($groep->samenvatting, 0, 100) . (strlen($groep->samenvatting) > 100 ? '...' : '' ) . '">' . $groep->naam . '</span>';
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['maker_uid'] = null;

		return parent::getJson($array);
	}

}

class GroepForm extends DataTableForm {

	public function __construct(Groep $groep, $action, $nocancel = false) {
		parent::__construct($groep, $action, get_class($groep) . ' ' . ($groep->id ? A::Wijzigen : A::Aanmaken));

		$fields = $this->generateFields();

		if (isset($fields['opvolg_naam'])) {
			$suggesties = array();
			foreach (Database::sqlSelect(array('DISTINCT opvolg_naam'), $groep->getTableName()) as $suggestie) {
				$suggesties[] = $suggestie[0];
			}
			$fields['opvolg_naam']->suggestions[] = $suggesties;
		}
		$fields['maker_uid']->readonly = !LoginModel::mag('P_ADMIN');

		$this->addFields(array(new FormDefaultKnoppen($nocancel ? false : null)));
	}

}

class GroepConverteerForm extends DataTableForm {

	public function __construct(Groep $groep, GroepenModel $model) {
		parent::__construct($groep, $groep->getUrl() . 'converteren', $model::orm . ' converteren');

		$options = array(
			'Met opvolgers'		 => array(
				'ActiviteitenModel'	 => ActiviteitenModel::orm,
				'WerkgroepenModel'	 => WerkgroepenModel::orm,
				'CommissiesModel'	 => CommissiesModel::orm,
				'BesturenModel'		 => BesturenModel::orm
			),
			'Zonder opvolgers'	 => array(
				'KetzersModel'			 => KetzersModel::orm,
				'WoonoordenModel'		 => WoonoordenModel::orm,
				'OnderverenigingenModel' => OnderverenigingenModel::orm,
				'GroepenModel'			 => GroepenModel::orm
			)
		);
		$fields[] = new SelectField('class', get_class($model), 'Converteren naar', $options, true);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
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
			$this->titel .= 'alle ' . lcfirst($model->getNaam());
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

	protected $url;
	protected $groepen;
	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct(GroepenModel $model, $groepen) {
		$this->groepen = $groepen;
		$this->url = $model->getUrl();
		$this->pagina = CmsPaginaModel::get($model->getNaam());
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
		}
	}

	public function view() {
		echo '<div class="float-right"><a class="btn" href="' . $this->url . 'beheren"><img class="icon" src="/plaetjes/famfamfam/table.png" width="16" height="16"> Beheren</a></div>';
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->groepen as $groep) {
			echo '<hr>';
			$view = new GroepView($groep, GroepTab::Pasfotos);
			$view->view();
		}
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
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
		if ($this->groep->maker_uid == 1025 AND $this->bb) {
			$html .= ' bb-dies2015';
		}
		$html .= '"><div class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		if ($this->groep->maker_uid == 1025) {
			$html .= '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M" class="float-left" style="margin-right: 10px;">';
		}
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
		$html .= '<div class="clear">&nbsp</div></div>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

}
