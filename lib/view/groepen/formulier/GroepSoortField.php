<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Enum;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\repository\groepen\OnderverenigingenRepository;
use CsrDelft\repository\groepen\RechtenGroepenRepository;
use CsrDelft\repository\groepen\WerkgroepenRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\keuzevelden\RadioField;

class GroepSoortField extends RadioField {

	public $columns = 1;
	protected $activiteit;
	protected $commissie;
	/**
	 * @var Groep
	 */
	private $groep;

	public function __construct(
		$name,
		$value,
		$description,
		Groep $groep
	) {
		parent::__construct($name, $value, $description, array());

		if ($groep instanceof HeeftSoort && $groep->getSoort() instanceof ActiviteitSoort) {
			$default = $groep->getSoort() ? $groep->getSoort() : ActiviteitSoort::Vereniging();
		} else {
			$default = ActiviteitSoort::Vereniging();
		}
		$this->activiteit = new EnumSelectField('activiteit', $default, null, ActiviteitSoort::class);
		$this->activiteit->onclick = <<<JS

$('#{$this->getId()}Option_ActiviteitenRepository').click();
JS;

		if ($groep instanceof Commissie) {
			$default = $groep->commissieSoort ?? CommissieSoort::Commissie();
		} else {
			$default = CommissieSoort::Commissie();
		}
		$this->commissie = new EnumSelectField('commissie', $default, null, CommissieSoort::class);
		$this->commissie->onclick = <<<JS

$('#{$this->getId()}Option_CommissiesRepository').click();
JS;

		$this->options = [
			ActiviteitenRepository::class => $this->activiteit,
			KetzersRepository::class => 'Aanschafketzer',
			WerkgroepenRepository::class => 'Werkgroep',
			RechtenGroepenRepository::class => 'Groep (overig)',
			OnderverenigingenRepository::class => 'Ondervereniging',
			WoonoordenRepository::class => 'Woonoord',
			BesturenRepository::class => 'Bestuur',
			CommissiesRepository::class => $this->commissie
		];
		$this->groep = $groep;
	}

	public function getSoort() {
		switch (parent::getValue()) {

			case 'ActiviteitenRepository':
				return $this->activiteit->getValue();

			case 'CommissiesRepository':
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
		/** @var Enum $soort */
		$soort = $this->getSoort();
		/**
		 * @Warning: Duplicate function in GroepForm->validate()
		 */
		if (!$this->groep->magAlgemeen(AccessAction::Beheren(), null, $soort)) {
			if ($model instanceof ActiviteitenRepository) {
				$naam = $soort->getDescription();
			} elseif ($model instanceof CommissiesRepository) {
				$naam = $soort->getDescription();
			} elseif ($model instanceof WoonoordenRepository) {
				$naam = $soort->getDescription();
			} else {
				$naam = $model->getNaam();
			}
			$this->error = 'U mag geen ' . $naam . ' aanmaken';
		}
		return $this->error === '';
	}

}
