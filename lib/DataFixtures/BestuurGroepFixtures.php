<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\profiel\Profiel;
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

	public function load(ObjectManager $manager)
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

		$this->maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_PRAESES),
			'Praeses'
		);
		$this->maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_ABACTIS),
			'Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_FISCUS),
			'Fiscus'
		);
		$this->maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_VICEABACTIS),
			'Vice-Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuur,
			$this->getReference(AccountFixtures::UID_BESTUUR_VICEPRAESES),
			'Vice-Praeses'
		);

		$this->maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_PRAESES),
			'Praeses'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_ABACTIS),
			'Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_FISCUS),
			'Fiscus'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_VICEABACTIS),
			'Vice-Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurOt,
			$this->getReference(AccountFixtures::UID_BESTUUR_OT_VICEPRAESES),
			'Vice-Praeses'
		);

		$this->maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_PRAESES),
			'Praeses'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_ABACTIS),
			'Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_FISCUS),
			'Fiscus'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_VICEABACTIS),
			'Vice-Abactis'
		);
		$this->maakGroepLid(
			$manager,
			$bestuurFt,
			$this->getReference(AccountFixtures::UID_BESTUUR_FT_VICEPRAESES),
			'Vice-Praeses'
		);

		$manager->flush();
	}

	private function maakGroepLid(
		ObjectManager $manager,
		Groep $groep,
		Profiel $profiel,
		string $opmerking
	) {
		$groepLid = new GroepLid();
		$groepLid->uid = $profiel->uid;
		$groepLid->profiel = $profiel;
		$groepLid->groep = $groep;
		$groepLid->groepId = $groep->id;
		$groepLid->opmerking = $opmerking;
		$groepLid->lidSinds = date_create_immutable();
		$groepLid->doorProfiel = $this->getReference(AccountFixtures::UID_PUBCIE);
		$groepLid->doorUid = $groepLid->doorProfiel->uid;

		$manager->persist($groepLid);
	}

	public function getDependencies()
	{
		return [AccountFixtures::class];
	}
}
