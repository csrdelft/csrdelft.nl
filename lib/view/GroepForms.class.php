<?php

/**
 * GroepForms.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepForm extends ModalForm {

	/**
	 * Aanmaken/Wijzigen
	 * @var AccessAction
	 */
	private $mode;

	public function __construct(AbstractGroep $groep, $action, $mode, $nocancel = false) {
		parent::__construct($groep, $action, get_class($groep), true);
		$this->mode = $mode;
		if ($groep->id) {
			$this->titel .= ' wijzigen';
		} else {
			$this->titel .= ' aanmaken';
		}
		$fields = $this->generateFields();

		$fields['familie']->title = 'Vul hier een \'achternaam\' in zodat de juiste ketzers elkaar opvolgen';
		$fields['familie']->suggestions[] = $groep->getFamilieSuggesties();
		$fields['omschrijving']->description = 'Meer lezen';

		$fields['begin_moment']->to_datetime = $fields['eind_moment'];
		$fields['eind_moment']->from_datetime = $fields['begin_moment'];

		if ($groep instanceof Activiteit) {
			$fields['eind_moment']->required = true;
		}
		if ($groep instanceof Ketzer) {
			$fields['aanmelden_vanaf']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->from_datetime = $fields['aanmelden_vanaf'];
			$fields['bewerken_tot']->title = 'Leden mogen hun eigen opmerking of keuze niet aanpassen als u dit veld leeg laat';
			$fields['afmelden_tot']->from_datetime = $fields['aanmelden_vanaf'];
			$fields['afmelden_tot']->title = 'Leden mogen zichzelf niet afmelden als u dit veld leeg laat';
			$fields['keuzelijst']->title = 'Zet | tussen de opties en gebruik && voor meerdere keuzelijsten';
		}
		if ($groep instanceof Kring) {
			unset($fields['samenvatting']);
		}

		$fields['maker_uid']->readonly = !LoginModel::mag('P_ADMIN');

		if (property_exists($groep, 'in_agenda') AND ! LoginModel::mag('P_AGENDA_MOD')) {
			unset($fields['in_agenda']);
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
		/**
		 * @Notice: Similar function in GroepSoortField->validate()
		 */
		if (!$groep::magAlgemeen($this->mode, $soort)) {
			if (!$groep->mag($this->mode, $soort)) {
				// beide aanroepen vanwege niet doorsturen van param $soort door mag() naar magAlgemeen()
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
			/**
			 * Omdat wijzigen wel is toegestaan met hetzelfde formulier
			 * en groep->mag() @runtime niet weet wat de orig value was (door form auto property set)
			 * op moment van uitvoeren van deze funtie, hier een extra check:
			 * 
			 * N.B.: Deze check staat binnen de !magAlgemeen zodat P_LEDEN_MOD deze check overslaat
			 */
			elseif ($this->mode === A::Wijzigen AND $groep instanceof Woonoord) {

				$origvalue = $this->findByName('soort')->getOrigValue();
				if ($origvalue !== $soort) {
					setMelding('U mag de huisstatus niet wijzigen', -1);
					return false;
				}
			}
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

class GroepOpvolgingForm extends ModalForm {

	public function __construct(AbstractGroep $groep, $action) {
		parent::__construct($groep, $action, 'Opvolging instellen', true);

		$fields['fam'] = new TextField('familie', $groep->familie, 'Familienaam');
		$fields['fam']->suggestions[] = $groep->getFamilieSuggesties();

		$options = array();
		foreach (GroepStatus::getTypeOptions() as $status) {
			$options[$status] = GroepStatus::getChar($status);
		}
		$fields[] = new RadioField('status', $groep->status, 'Groepstatus', $options);

		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

class GroepSoortField extends RadioField {

	public $columns = 1;
	protected $activiteit;
	protected $commissie;

	public function __construct($name, $value, $description, AbstractGroep $groep) {
		parent::__construct($name, $value, $description, array());

		require_once 'model/entity/groepen/ActiviteitSoort.enum.php';
		$activiteiten = array();
		foreach (ActiviteitSoort::getTypeOptions() as $soort) {
			$activiteiten[$soort] = ActiviteitSoort::getDescription($soort);
		}
		if (property_exists($groep, 'soort') AND in_array($groep->soort, $activiteiten)) {
			$default = $groep->soort;
		} else {
			$default = ActiviteitSoort::Vereniging;
		}
		$this->activiteit = new SelectField('activiteit', $default, null, $activiteiten);
		$this->activiteit->onclick = <<<JS

$('#{$this->getId()}Option_ActiviteitenModel').click();
JS;

		require_once 'model/entity/groepen/CommissieSoort.enum.php';
		$commissies = array();
		foreach (CommissieSoort::getTypeOptions() as $soort) {
			$commissies[$soort] = CommissieSoort::getDescription($soort);
		}
		if (property_exists($groep, 'soort') AND in_array($groep->soort, $commissies)) {
			$default = $groep->soort;
		} else {
			$default = CommissieSoort::Commissie;
		}
		$this->commissie = new SelectField('commissie', $default, null, $commissies);
		$this->commissie->onclick = <<<JS

$('#{$this->getId()}Option_CommissiesModel').click();
JS;

		$this->options = array(
			'ActiviteitenModel'		 => $this->activiteit,
			'KetzersModel'			 => 'Aanschafketzer',
			'WerkgroepenModel'		 => WerkgroepenModel::orm,
			'RechtenGroepenModel'	 => 'Groep (overig)',
			'OnderverenigingenModel' => OnderverenigingenModel::orm,
			'WoonoordenModel'		 => WoonoordenModel::orm,
			'BesturenModel'			 => BesturenModel::orm,
			'CommissiesModel'		 => $this->commissie
		);
	}

	public function getSoort() {
		switch (parent::getValue()) {

			case 'ActiviteitenModel': return $this->activiteit->getValue();

			case 'CommissiesModel': return $this->commissie->getValue();

			default: return null;
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$class = $this->value;
		$model = $class::instance(); // require once
		$orm = $model::orm;
		$soort = $this->getSoort();
		/**
		 * @Warning: Duplicate function in GroepForm->validate()
		 */
		if (!$orm::magAlgemeen($this->mode, $soort)) {
			if ($model instanceof ActiviteitenModel) {
				$naam = ActiviteitSoort::getDescription($soort);
			} elseif ($model instanceof CommissiesModel) {
				$naam = CommissieSoort::getDescription($soort);
			} elseif ($model instanceof WoonoordenModel) {
				$naam = HuisStatus::getDescription($soort);
			} else {
				$naam = $model->getNaam();
			}
			$this->error = 'U mag geen ' . $naam . ' aanmaken';
		}
		return $this->error === '';
	}

}

class KetzerSoortField extends GroepSoortField {

	public $columns = 2;

	public function __construct($name, $value, $description, AbstractGroep $groep) {
		parent::__construct($name, $value, $description, $groep);

		$this->options = array();
		foreach ($this->activiteit->getOptions() as $soort => $label) {
			$this->options['ActiviteitenModel_' . $soort] = $label;
		}
		$this->options['KetzersModel'] = 'Aanschafketzer';
		//$this->options['WerkgroepenModel'] = WerkgroepenModel::orm;
		//$this->options['RechtenGroepenModel'] = 'Groep (overig)';
	}

	/**
	 * Super ugly
	 * @return boolean
	 */
	public function validate() {
		$class = explode('_', $this->value, 2);
		$soort = null;
		switch ($class[0]) {

			case 'ActiviteitenModel':
				$soort = $class[1];
			// fall through

			case 'KetzersModel':
				$model = $class[0]::instance(); // require once
				$orm = $model::orm;
				if (!$orm::magAlgemeen(A::Aanmaken, $soort)) {
					if ($model instanceof ActiviteitenModel) {
						$naam = ActiviteitSoort::getDescription($soort);
					} else {
						$naam = $model->getNaam();
					}
					$this->error = 'U mag geen ' . $naam . ' aanmaken';
				}
				break;

			default:
				$this->error = 'Onbekende optie gekozen';
		}
		return $this->error === '';
	}

}

class GroepConverteerForm extends ModalForm {

	public function __construct(AbstractGroep $groep, AbstractGroepenModel $huidig) {
		parent::__construct($groep, $huidig->getUrl() . 'converteren', $huidig::orm . ' converteren', true);

		$fields[] = new GroepSoortField('model', get_class($huidig), 'Converteren naar', $groep);

		$fields['btn'] = new FormDefaultKnoppen();
		$fields['btn']->submit->icon = '/famfamfam/lightning.png';
		$fields['btn']->submit->label = 'Converteren';

		$this->addFields($fields);
	}

	public function getValues() {
		$values = parent::getValues();
		$values['soort'] = $this->findByName('model')->getSoort();
		return $values;
	}

}

class GroepAanmakenForm extends ModalForm {

	public function __construct(AbstractGroepenModel $huidig, $soort = null) {
		$groep = $huidig->nieuw($soort);
		parent::__construct($groep, $huidig->getUrl() . 'nieuw', 'Nieuwe ketzer aanmaken');
		$this->css_classes[] = 'redirect';

		$default = get_class($huidig);
		if (property_exists($groep, 'soort')) {
			$default .= '_' . $groep->soort;
		}
		$fields[] = new KetzerSoortField('model', $default, null, $groep);

		$fields['btn'] = new FormDefaultKnoppen(null, false);
		$fields['btn']->submit->icon = '/famfamfam/add.png';
		$fields['btn']->submit->label = 'Aanmaken';

		$this->addFields($fields);
	}

	public function getValues() {
		$return = array();
		$value = $this->findByName('model')->getValue();
		$values = explode('_', $value, 2);
		$return['model'] = $values[0];
		if (isset($values[1])) {
			$return['soort'] = $values[1];
		} else {
			$return['soort'] = null;
		}
		return $return;
	}

}

class GroepLidBeheerForm extends ModalForm {

	public function __construct(AbstractGroepLid $lid, $action, array $blacklist = null) {
		parent::__construct($lid, $action, 'Aanmelding bewerken', true);
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

		$fields[] = new FormDefaultKnoppen();
		$this->addFields($fields);
	}

}

class GroepBewerkenForm extends InlineForm {

	public function __construct(AbstractGroepLid $lid, AbstractGroep $groep, $toggle = true, $buttons = true) {

		if ($groep->keuzelijst) {
			$field = new MultiSelectField('opmerking', $lid->opmerking, null, $groep->keuzelijst);
		} else {
			$field = new TextField('opmerking', $lid->opmerking, null);
			$field->placeholder = 'Opmerking';
			$field->suggestions[] = $groep->getOpmerkingSuggesties();
		}

		parent::__construct($lid, $groep->getUrl() . 'bewerken/' . $lid->uid, $field, $toggle, $buttons);
	}

}

class GroepAanmeldKnoppen extends FormKnoppen {

	public $submit;

	public function __construct($pasfoto = false) {
		parent::__construct();
		if ($pasfoto) {
			$this->submit = new PasfotoAanmeldenKnop();
		} else {
			$this->submit = new SubmitKnop(null, 'submit', 'Aanmelden', null, null);
		}
		$this->addKnop($this->submit, true);
	}

}

class GroepAanmeldenForm extends GroepBewerkenForm {

	public function __construct(AbstractGroepLid $lid, AbstractGroep $groep, $pasfoto = true) {
		parent::__construct($lid, $groep, false, new GroepAanmeldKnoppen($pasfoto));

		$this->action = $groep->getUrl() . 'aanmelden/' . $lid->uid;
		$this->css_classes[] = 'float-left';

		if ($pasfoto) {
			$this->getField()->hidden = true;
		}
	}

}

class GroepLogboekForm extends ModalForm {

	public function __construct(AbstractGroep $groep) {
		parent::__construct($groep, null, $groep->naam . ' logboek', true);

		$fields[] = new GroepLogboekTable($groep);
		$fields[] = new ModalCloseButtons();

		$this->addFields($fields);
	}

}

class GroepPreviewForm extends ModalForm implements FormElement {

	public function __construct(AbstractGroep $groep) {
		parent::__construct($groep, null, 'Voorbeeldweergave');

		$fields[] = new HtmlBbComment('<div style="max-width: 580px;">Gebruik de volgende code in uw forumbericht voor onderstaand resultaat: [code][' . get_class($groep) . '=' . $groep->id . '][/code][rn]');
		$fields[] = new GroepView($groep, null, false, true);
		$fields[] = new HtmlComment('</div>');
		$fields[] = new ModalCloseButtons();

		$this->addFields($fields);
	}

	public function getHtml() {
		$this->css_classes[] = 'ModalForm';
		$html = getMelding();
		$html .= $this->getFormTag();
		if ($this->getTitel()) {
			$html .= '<h1 class="Titel">' . $this->getTitel() . '</h1>';
		}
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form>';
	}

	public function getJavascript() {
		parent::getJavascript();
	}

	public function getType() {
		return get_class($this->model);
	}

}
