<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\GroepRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommissiesRepository extends GroepRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Commissie::class);
	}

	public function nieuw($soort = null) {
		if (!$soort || !in_array($soort, CommissieSoort::getEnumValues())) {
			$soort = CommissieSoort::Commissie;
		}
		/** @var Commissie $commissie */
		$commissie = parent::nieuw();
		$commissie->commissieSoort = $soort;
		return $commissie;
	}

	public function overzicht(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return $this->findBy(['status' => GroepStatus::HT(), 'commissieSoort' => CommissieSoort::from($soort)]);
		}
		return parent::overzicht($soort);
	}

	public function beheer(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return $this->findBy(['commissieSoort' => CommissieSoort::from($soort)]);
		}
		return parent::beheer($soort);
	}


	public function parseSoort(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return CommissieSoort::from($soort);
		}
		return parent::parseSoort($soort);
	}

}
