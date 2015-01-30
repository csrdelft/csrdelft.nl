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

	private $url;
	private $naam;

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'familie');

		$this->url = $model->getUrl();
		$this->dataUrl = $this->url . 'beheren';

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

		$create = new DataTableKnop('== 0', $this->tableId, $this->url . 'aanmaken', 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $this->url . 'wijzigen', 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		$opvolg = new DataTableKnop('>= 1', $this->tableId, $this->url . 'opvolging', 'post popup', 'Opvolging', 'Familienaam en groepstatus instellen', 'timeline');
		$this->addKnop($opvolg);

		$convert = new DataTableKnop('>= 1', $this->tableId, $this->url . 'converteren', 'post popup', 'Converteren', 'Converteer groep', 'lightning');
		$this->addKnop($convert);

		$delete = new DataTableKnop('>= 1', $this->tableId, $this->url . 'verwijderen', 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <a href="' . $this->url . '">' . $this->naam . '</a> » <span class="active">Beheren</span>';
	}

	public function view() {
		$view = new CmsPaginaView(CmsPaginaModel::get($this->naam));
		$view->view();
		parent::view();
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['detailSource'] = $groep->getUrl() . 'leden';

		if ($groep->mag(A::Rechten)) {
			$array['id'] .= '<a href="/rechten/bekijken/' . get_class($groep) . '/' . $groep->id . '" class="float-right" title="Rechten aanpassen"><img width="16" height="16" class="icon" src="/plaetjes/famfamfam/bullet_key.png"></a>';
		}

		$array['naam'] = '<span title="' . $groep->naam . (empty($groep->samenvatting) ? '' : '&#13;&#13;') . mb_substr($groep->samenvatting, 0, 100) . (strlen($groep->samenvatting) > 100 ? '...' : '' ) . '">' . $groep->naam . '</span>';
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['maker_uid'] = null;

		return parent::getJson($array);
	}

}

class GroepForm extends DataTableForm {

	public function __construct(Groep $groep, $action, $nocancel = false) {
		parent::__construct($groep, $action, get_class($groep) . ' ' . ($groep->id ? 'wijzigen' : 'aanmaken'));

		$fields = $this->generateFields();

		if (isset($fields['familie'])) {
			$fields['familie']->suggestions[] = $groep->getOpvolgingSuggesties();
		}
		$fields['maker_uid']->readonly = !LoginModel::mag('P_ADMIN');

		$this->addFields(array(new FormDefaultKnoppen($nocancel ? false : null)));
	}

}

class GroepOpvolgingForm extends DataTableForm {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, $action, 'Opvolging instellen');

		$fields['fam'] = new TextField('familie', $groep->familie, 'Familienaam');
		$fields['fam']->suggestions[] = $groep->getOpvolgingSuggesties();

		$options = array();
		foreach (GroepStatus::getTypeOptions() as $status) {
			$options[$status] = GroepStatus::getChar($status);
		}
		$fields[] = new KeuzeRondjeField('status', $groep->status, 'Groepstatus', $options);

		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

class GroepConverteerForm extends DataTableForm {

	public function __construct(Groep $groep, GroepenModel $model) {
		parent::__construct($groep, $groep->getUrl() . 'converteren', $model::orm . ' converteren');

		$options = array(
			'ActiviteitenModel'		 => ActiviteitenModel::orm,
			'BesturenModel'			 => BesturenModel::orm,
			'CommissiesModel'		 => CommissiesModel::orm,
			'GroepenModel'			 => GroepenModel::orm,
			'KetzersModel'			 => KetzersModel::orm,
			'OnderverenigingenModel' => OnderverenigingenModel::orm,
			'WerkgroepenModel'		 => WerkgroepenModel::orm,
			'WoonoordenModel'		 => WoonoordenModel::orm
		);
		$fields[] = new SelectField('class', get_class($model), 'Converteren naar', $options);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

class GroepenView implements View {

	private $url;
	private $tab;
	private $groepen;
	/**
	 * Toon CMS pagina
	 * @var string
	 */
	private $pagina;

	public function __construct(GroepenModel $model, $groepen) {
		$this->groepen = $groepen;
		$this->url = $model->getUrl();
		$this->pagina = CmsPaginaModel::get($model->getNaam());
		if ($model instanceof BesturenModel) {
			$this->tab = GroepTab::Lijst;
		} else {
			$this->tab = GroepTab::Pasfotos;
		}
	}

	public function view() {
		echo '<div class="float-right"><a class="btn" href="' . $this->url . 'beheren"><img class="icon" src="/plaetjes/famfamfam/table.png" width="16" height="16"> Beheren</a></div>';
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->groepen as $groep) {
			// Controleer rechten
			if (!$groep->mag(A::Bekijken)) {
				continue;
			}
			echo '<hr>';
			$view = new GroepView($groep, $this->tab);
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
		$html .= '"><div id="groep-samenvatting-' . $this->groep->id . '" class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		if ($this->groep->maker_uid == 1025) {
			$html .= '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M" class="float-left" style="margin-right: 10px;">';
		}
		$html .= CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			$html .= '<div class="clear">&nbsp;</div><a id="groep-omschrijving-' . $this->groep->id . '" class="post noanim" href="' . $this->groep->getUrl() . 'omschrijving">Meer lezen »</a>';
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
