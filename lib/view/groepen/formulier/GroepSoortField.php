<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Enum;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\keuzevelden\RadioField;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class GroepSoortField extends RadioField
{
	public $columns = 1;
	protected $activiteit;
	protected $commissie;
	/**
	 * @var Groep
	 */
	private $groep;
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
		parent::__construct($name, $value, $description, []);

		if (
			$groep instanceof HeeftSoort &&
			$groep->getSoort() instanceof ActiviteitSoort
		) {
			$default = $groep->getSoort()
				? $groep->getSoort()
				: ActiviteitSoort::Vereniging();
		} else {
			$default = ActiviteitSoort::Vereniging();
		}
		$this->activiteit = new EnumSelectField(
			'activiteit',
			$default,
			null,
			ActiviteitSoort::class
		);
		$this->activiteit->onclick = <<<JS

$('#{$this->getId()}Option_ActiviteitenRepository').click();
JS;

		if ($groep instanceof Commissie) {
			$default = $groep->commissieSoort ?? CommissieSoort::Commissie();
		} else {
			$default = CommissieSoort::Commissie();
		}
		$this->commissie = new EnumSelectField(
			'commissie',
			$default,
			null,
			CommissieSoort::class
		);
		$this->commissie->onclick = <<<JS

$('#{$this->getId()}Option_CommissiesRepository').click();
JS;

		$this->options = [
			Activiteit::class => $this->activiteit,
			Ketzer::class => 'Aanschafketzer',
			Werkgroep::class => 'Werkgroep',
			RechtenGroep::class => 'Groep (overig)',
			Ondervereniging::class => 'Ondervereniging',
			Woonoord::class => 'Woonoord',
			Bestuur::class => 'Bestuur',
			Commissie::class => $this->commissie,
		];
		$this->groep = $groep;
		$this->doctrine = $doctrine;
	}

	public function getSoort()
	{
		switch (parent::getValue()) {
			case Activiteit::class:
				return $this->activiteit->getValue();

			case Commissie::class:
				return $this->commissie->getValue();

			default:
				return null;
		}
	}

	public function validate(): bool
	{
		if (!parent::validate()) {
			return false;
		}
		$class = $this->value;
		$model = $this->doctrine->getRepository($class);
		/** @var Enum $soort */
		$soort = $this->getSoort();
		/**
		 * @Warning: Duplicate function in GroepForm->validate()
		 */
		$security = ContainerFacade::getContainer()->get('security');

		$testGroep = new $class();
		if ($testGroep instanceof HeeftSoort) {
			$testGroep->setSoort($soort);
		}

		if (!$security->isGranted(AbstractGroepVoter::BEHEREN, $testGroep)) {
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
