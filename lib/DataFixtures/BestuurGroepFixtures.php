<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\GroepFixtureUtil;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\groepen\BesturenRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BestuurGroepFixtures extends Fixture implements DependentFixtureInterface
{
	/**
	 * @var BesturenRepository
	 */
	private $besturenRepository;

	public function __construct(BesturenRepository $besturenRepository)
	{
		$this->besturenRepository = $besturenRepository;
	}

	public function load(ObjectManager $manager): void
	{
		$bestuur = $this->besturenRepository->nieuw();
		$bestuur->naam = 'Bestuur 1';
		$bestuur->samenvatting = "Onder het motto: \"Samen één\".";
		$bestuur->oudId = 1;
		$bestuur->status = GroepStatus::HT();
		$bestuur->familie = 'Bestuur';

		$manager->persist($bestuur);

		$manager->flush();

		$bestuurOt = $this->besturenRepository->nieuw();
		$bestuurOt->naam = 'Bestuur 0';
		$bestuurOt->samenvatting = 'Vroeger was alles beter';
		$bestuurOt->oudId = 0;
		$bestuurOt->status = GroepStatus::OT();
		$bestuurOt->familie = 'Bestuur';

		$manager->persist($bestuurOt);
		$manager->flush();

		$bestuurFt = $this->besturenRepository->nieuw();
		$bestuurFt->naam = 'Bestuur 2';
		$bestuurFt->samenvatting = 'Samen is beter dan alleen';
		$bestuurFt->oudId = 2;
		$bestuurFt->status = GroepStatus::FT();
		$bestuurFt->familie = 'Bestuur';

		$manager->persist($bestuurFt);
		$manager->flush();

		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_PRAESES),
			'Praeses'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_ABACTIS),
			'Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_FISCUS),
			'Fiscus'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_VICEABACTIS),
			'Vice-Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_VICEPRAESES),
			'Vice-Praeses'
		);

		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_PRAESES),
			'Praeses'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_ABACTIS),
			'Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_FISCUS),
			'Fiscus'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_VICEABACTIS),
			'Vice-Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_VICEPRAESES),
			'Vice-Praeses'
		);

		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_PRAESES),
			'Praeses'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_ABACTIS),
			'Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_FISCUS),
			'Fiscus'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_VICEABACTIS),
			'Vice-Abactis'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_VICEPRAESES),
			'Vice-Praeses'
		);

		$manager->flush();
	}

	public function getDependencies(): array
	{
		return [AccountFixtures::class];
	}
}
