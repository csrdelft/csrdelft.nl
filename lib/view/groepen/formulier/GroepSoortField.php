<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\CommissieSoort;
use CsrDelft\entity\groepen\ActiviteitSoort;
use CsrDelft\entity\groepen\HuisStatus;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\repository\groepen\OnderverenigingenRepository;
use CsrDelft\repository\groepen\RechtenGroepenRepository;
use CsrDelft\repository\groepen\WerkgroepenRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\view\formulier\keuzevelden\RadioField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use function common\short_class;

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

		$this->options = [
			ActiviteitenRepository::class => $this->activiteit,
			KetzersRepository::class => 'Aanschafketzer',
			WerkgroepenRepository::class => short_class(WerkgroepenRepository::ORM),
			RechtenGroepenRepository::class => 'Groep (overig)',
			OnderverenigingenRepository::class => short_class(OnderverenigingenRepository::ORM),
			WoonoordenRepository::class => short_class(WoonoordenRepository::ORM),
			BesturenRepository::class => short_class(BesturenRepository::ORM),
			CommissiesRepository::class => $this->commissie
		];
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
		$model = ContainerFacade::getContainer()->get($class);
		$orm = $model::ORM;
		$soort = $this->getSoort();
		/**
		 * @Warning: Duplicate function in GroepForm->validate()
		 */
		if (!$orm::magAlgemeen(AccessAction::Beheren, null, $soort)) {
			if ($model instanceof ActiviteitenRepository) {
				$naam = ActiviteitSoort::getDescription($soort);
			} elseif ($model instanceof CommissiesRepository) {
				$naam = CommissieSoort::getDescription($soort);
			} elseif ($model instanceof WoonoordenRepository) {
				$naam = HuisStatus::getDescription($soort);
			} else {
				$naam = $model->getNaam();
			}
			$this->error = 'U mag geen ' . $naam . ' aanmaken';
		}
		return $this->error === '';
	}

}
