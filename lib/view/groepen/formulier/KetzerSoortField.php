<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\GroepRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class KetzerSoortField extends GroepSoortField
{
	public $columns = 2;
	/**
	 * @var ManagerRegistry
	 */
	private $doctrine;

	public function __construct(
		ManagerRegistry $doctrine,
		$name,
		$value,
		$description,
		Groep $groep
	) {
		parent::__construct($doctrine, $name, $value, $description, $groep);

		$this->options = [];
		foreach ($this->activiteit->getOptions() as $soort => $label) {
			$this->options[Activiteit::class . '_' . $soort] = $label;
		}
		$this->options[Ketzer::class] = 'Aanschafketzer';
		//$this->options['WerkgroepenRepository'] = 'Werkgroep';
		//$this->options['RechtenGroepenRepository'] = 'Groep (overig)';
		$this->doctrine = $doctrine;
	}

	/**
	 * Pretty ugly
	 * @return boolean
	 */
	public function validate()
	{
		$class = explode('_', $this->value, 2);

		if ($class[0] === Activiteit::class) {
			$soort = $class[1];
		} elseif ($class[0] === Ketzer::class) {
			$soort = null;
		} else {
			$this->error = 'Onbekende optie gekozen';
			return false;
		}

		/** @var GroepRepository $model */
		$model = $this->doctrine->getRepository($class[0]);
		/** @var Groep|string $orm */
		$orm = $model->getClassName();
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
