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
		$this->titel = 'Beheer ' . $this->naam;

		$this->hideColumn('id', false);
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('maker_uid');
		$this->hideColumn('keuzelijst');
		$this->hideColumn('rechten_aanmelden');
		$this->hideColumn('status_historie');
		$this->searchColumn('naam');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$create = new DataTableKnop('== 0', $this->tableId, $this->url . 'aanmaken', 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, $this->url . 'wijzigen', 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		if (property_exists($model::orm, 'aanmelden_vanaf')) {
			$sluiten = new DataTableKnop('>= 1', $this->tableId, $this->url . 'sluiten', 'post confirm', 'Sluiten', 'Inschrijvingen nu sluiten', 'lock');
			$this->addKnop($sluiten);
		}

		$opvolg = new DataTableKnop('>= 1', $this->tableId, $this->url . 'opvolging', 'post popup', 'Opvolging', 'Familienaam en groepstatus instellen', 'timeline');
		$this->addKnop($opvolg);

		$convert = new DataTableKnop('>= 1', $this->tableId, $this->url . 'converteren', 'post popup', 'Converteren', 'Converteer groep', 'lightning');
		$this->addKnop($convert);

		$delete = new DataTableKnop('>= 1', $this->tableId, $this->url . 'verwijderen', 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <a href="' . $this->url . '">' . ucfirst($this->naam) . '</a> » <span class="active">Beheren</span>';
	}

	public function view() {
		$view = new CmsPaginaView(CmsPaginaModel::get($this->naam));
		$view->view();
		parent::view();
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		// Controleer rechten
		$array = $groep->jsonSerialize();

		$array['detailSource'] = $groep->getUrl() . 'leden';
		$array['naam'] = '<span title="' . $groep->naam . (empty($groep->samenvatting) ? '' : '&#13;&#13;') . mb_substr($groep->samenvatting, 0, 100) . (strlen($groep->samenvatting) > 100 ? '...' : '' ) . '">' . $groep->naam . '</span>';
		$array['status'] = GroepStatus::getChar($groep->status);
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

		$fields['familie']->title = 'Vul hier een \'achternaam\' in zodat de juiste ketzers elkaar opvolgen';
		$fields['familie']->suggestions[] = $groep->getFamilieSuggesties();
		$fields['omschrijving']->description = 'Meer lezen';

		$fields['begin_moment']->to_datetime = $fields['eind_moment'];
		$fields['eind_moment']->from_datetime = $fields['begin_moment'];

		if ($groep instanceof Ketzer) {
			$fields['aanmelden_vanaf']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->from_datetime = $fields['aanmelden_vanaf'];
			$fields['afmelden_tot']->from_datetime = $fields['aanmelden_vanaf'];
		}

		$fields['maker_uid']->readonly = !LoginModel::mag('P_ADMIN');

		if (property_exists($groep, 'in_agenda') AND ! LoginModel::mag('P_AGENDA_MOD')) {
			unset($fields['in_agenda']);
		}

		if (property_exists($groep, 'rechten_aanmelden')) {
			$profiel = LoginModel::getProfiel();
			$lidjaar = $profiel->lidjaar;
			$verticale = $profiel->verticale;
			if ($profiel->geslacht === Geslacht::Vrouw) {
				$onder = 'geslacht:v';
			} else {
				$onder = 'ondervereniging:naam';
			}
			$fields['soort']->onchange = <<<JS

$('#{$fields['rechten_aanmelden']->getId()}').val(function() {
	switch($('#{$fields['soort']->getId()}').val()) {

		case 'ondervereniging':
			return '{$onder}';

		case 'lichting':
			return 'lichting:{$lidjaar}';

		case 'verticale':
			return 'verticale:{$verticale}';

		case 'kring':
			return 'TODO';

		default:
			return '';
	}
});
JS;
		}

		$fields[] = $etc[] = new FormDefaultKnoppen($nocancel ? false : null);
		$this->addFields($fields);
	}

	public function validate() {
		$groep = $this->getModel();
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		} else {
			$soort = null;
		}
		if (!$groep::magAlgemeen(A::Aanmaken, $soort)) {
			if ($groep instanceof Activiteit) {
				$naam = ActiviteitSoort::getDescription($soort);
			} elseif ($groep instanceof Commissie) {
				$naam = CommissieSoort::getDescription($soort);
			} else {
				$naam = get_class($groep);
			}
			setMelding('U mag geen ' . $naam . ' aanmaken', -1);
			return false;
		}

		$fields = $this->getFields();
		if ($fields['eind_moment']->getValue() !== null AND strtotime($fields['eind_moment']->getValue()) < strtotime($fields['begin_moment']->getValue())) {
			$fields['eind_moment']->error = 'Eindmoment moet na beginmoment liggen';
		}
		if ($groep instanceof Ketzer) {
			if ($fields['afmelden_tot']->getValue() !== null AND strtotime($fields['afmelden_tot']->getValue()) < strtotime($fields['aanmelden_vanaf']->getValue())) {
				$fields['afmelden_tot']->error = 'Afmeldperiode moet eindigen na begin aanmeldperiode';
			}
			if ($fields['bewerken_tot']->getValue() !== null AND strtotime($fields['bewerken_tot']->getValue()) < strtotime($fields['aanmelden_vanaf']->getValue())) {
				$fields['bewerken_tot']->error = 'Bewerkenperiode moet eindigen na begin aanmeldperiode';
			}
		}

		return parent::validate();
	}

}

class GroepOpvolgingForm extends DataTableForm {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, $action, 'Opvolging instellen');

		$fields['fam'] = new TextField('familie', $groep->familie, 'Familienaam');
		$fields['fam']->suggestions[] = $groep->getFamilieSuggesties();

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

	private $activiteit;
	private $commissie;

	public function __construct(Groep $groep, GroepenModel $model) {
		parent::__construct($groep, $model->getUrl() . 'converteren', $model::orm . ' converteren');
		$huidig = get_class($model);

		require_once 'model/entity/groepen/ActiviteitSoort.enum.php';
		$soorten = array();
		foreach (ActiviteitSoort::getTypeOptions() as $soort) {
			$soorten[$soort] = ActiviteitSoort::getDescription($soort);
		}
		if (property_exists($groep, 'soort') AND in_array($groep->soort, $soorten)) {
			$default = $groep->soort;
		} else {
			$default = ActiviteitSoort::Vereniging;
		}
		$this->activiteit = new SelectField('activiteit', $default, null, $soorten);

		require_once 'model/entity/groepen/CommissieSoort.enum.php';
		$soorten = array();
		foreach (CommissieSoort::getTypeOptions() as $soort) {
			$soorten[$soort] = CommissieSoort::getDescription($soort);
		}
		if (property_exists($groep, 'soort') AND in_array($groep->soort, $soorten)) {
			$default = $groep->soort;
		} else {
			$default = CommissieSoort::Commissie;
		}
		$this->commissie = new SelectField('commissie', $default, null, $soorten);

		$options = array(
			'ActiviteitenModel'		 => $this->activiteit,
			'KetzersModel'			 => 'Ketzer (diversen)',
			'WerkgroepenModel'		 => WerkgroepenModel::orm,
			'OnderverenigingenModel' => OnderverenigingenModel::orm,
			'WoonoordenModel'		 => WoonoordenModel::orm,
			'BesturenModel'			 => BesturenModel::orm,
			'CommissiesModel'		 => $this->commissie,
			'GroepenModel'			 => 'Overige groep'
		);
		$model = new KeuzeRondjeField('model', $huidig, 'Converteren naar', $options, true);
		$model->newlines = true;

		$this->activiteit->onclick = <<<JS

$('#{$model->getId()}Option_ActiviteitenModel').click();
JS;
		$this->commissie->onclick = <<<JS

$('#{$model->getId()}Option_CommissiesModel').click();
JS;

		$fields[] = $model;
		$fields[] = new FormDefaultKnoppen();
		$this->addFields($fields);
	}

	public function getValues() {
		$values = parent::getValues();
		switch ($values['model']) {

			case 'ActiviteitenModel':
				$values['soort'] = $this->activiteit->getValue();
				break;

			case 'CommissiesModel':
				$values['soort'] = $this->commissie->getValue();
				break;

			default:
				$values['soort'] = null;
		}
		return $values;
	}

	public function validate() {
		$values = $this->getValues();
		$model = $values['model']::instance(); // require once
		$orm = $model::orm;
		if (!$orm::magAlgemeen(A::Aanmaken, $values['soort'])) {
			if ($model instanceof ActiviteitenModel) {
				$naam = ActiviteitSoort::getDescription($values['soort']);
			} elseif ($model instanceof CommissiesModel) {
				$naam = CommissieSoort::getDescription($values['soort']);
			} else {
				$naam = $model->getNaam();
			}
			setMelding('U mag geen ' . $naam . ' aanmaken', -1);
			return false;
		}

		return parent::validate();
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
