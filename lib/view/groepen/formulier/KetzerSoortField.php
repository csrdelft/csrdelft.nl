<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\repository\GroepRepository;

class KetzerSoortField extends GroepSoortField
{
	public $columns = 2;

	public function __construct($name, $value, $description, Groep $groep)
	{
		parent::__construct($name, $value, $description, $groep);

		$this->options = [];
		foreach ($this->activiteit->getOptions() as $soort => $label) {
			$this->options[ActiviteitenRepository::class . '_' . $soort] = $label;
		}
		$this->options[KetzersRepository::class] = 'Aanschafketzer';
		//$this->options['WerkgroepenRepository'] = 'Werkgroep';
		//$this->options['RechtenGroepenRepository'] = 'Groep (overig)';
	}

	/**
	 * Pretty ugly
	 * @return boolean
	 */
	public function validate()
	{
		$class = explode('_', $this->value, 2);

		if ($class[0] === ActiviteitenRepository::class) {
			$soort = $class[1];
		} elseif ($class[0] === KetzersRepository::class) {
			$soort = null;
		} else {
			$this->error = 'Onbekende optie gekozen';
			return false;
		}

		/** @var GroepRepository $model */
		$model = ContainerFacade::getContainer()->get($class[0]); // require once
		$orm = $model->entityClass;
		if (!$orm::magAlgemeen(AccessAction::Aanmaken(), $soort)) {
			if ($model instanceof ActiviteitenRepository) {
				$naam = ActiviteitSoort::from($soort)->getDescription();
			} else {
				$naam = $model->getNaam();
			}
			$this->error = 'U mag geen ' . $naam . ' aanmaken';
		}

		return true;
	}
}
