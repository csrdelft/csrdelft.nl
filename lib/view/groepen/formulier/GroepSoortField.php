<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\groepen\CommissieSoort;
use CsrDelft\model\entity\groepen\HuisStatus;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\view\formulier\keuzevelden\RadioField;
use CsrDelft\view\formulier\keuzevelden\SelectField;

class GroepSoortField extends RadioField {

	public $columns = 1;
	protected $activiteit;
	protected $commissie;

	public function __construct(
		$name,
		$value,
		$description,
		AbstractGroep $groep
	) {
		parent::__construct($name, $value, $description, array());

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
			ActiviteitenModel::class => $this->activiteit,
			KetzersModel::class => 'Aanschafketzer',
			WerkgroepenModel::class => WerkgroepenModel::ORM,
			RechtenGroepenModel::class => 'Groep (overig)',
			OnderverenigingenModel::class => OnderverenigingenModel::ORM,
			WoonoordenModel::class => WoonoordenModel::ORM,
			BesturenModel::class => BesturenModel::ORM,
			CommissiesModel::class => $this->commissie
		);
	}

	public function getSoort() {
		switch (parent::getValue()) {

			case 'ActiviteitenModel':
				return $this->activiteit->getValue();

			case 'CommissiesModel':
				return $this->commissie->getValue();

			default:
				return null;
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$class = $this->value;
		$model = $class::instance(); // require once
		$orm = $model::ORM;
		$soort = $this->getSoort();
		/**
		 * @Warning: Duplicate function in GroepForm->validate()
		 */
		if (!$orm::magAlgemeen($this->mode, null, $soort)) {
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
