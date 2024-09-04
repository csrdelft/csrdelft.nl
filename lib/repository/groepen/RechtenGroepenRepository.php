<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class RechtenGroepenRepository extends GroepRepository
{
	public function __construct(
		private readonly BesturenRepository $besturenRepository,
		private readonly CommissiesRepository $commissiesRepository,
		private readonly GroepLidRepository $groepLidRepository,
		Security $security,
		ManagerRegistry $registry
	) {
		parent::__construct($registry, $security);
	}

	public function getEntityClassName()
	{
		return RechtenGroep::class;
	}

	public function nieuw($soort = null)
	{
		/** @var RechtenGroep $groep */
		$groep = parent::nieuw();
		$groep->rechtenAanmelden = P_LEDEN_MOD;
		return $groep;
	}

	public static function getNaam()
	{
		return 'overig';
	}

	/**
	 * Groepen waarvan de gevraagde gebruiker de wikipagina's mag lezen en bewerken.
	 *
	 * @param string $uid
	 * @return array
	 */
	public function getWikiToegang($uid)
	{
		$result = [];
		$profiel = ProfielRepository::get($uid);
		if (!$profiel) {
			return $result;
		}
		if ($profiel->isLid() or $profiel->isOudlid()) {
			$result[] = 'htleden-oudleden';
		}
		// 1 generatie vooruit en 1 achteruit (default order by)
		$ft = $this->besturenRepository->findOneBy(['status' => GroepStatus::FT()]);
		$ht = $this->besturenRepository->findOneBy(['status' => GroepStatus::HT()]);
		$ot = $this->besturenRepository->findOneBy(
			['status' => GroepStatus::OT()],
			['id' => 'DESC']
		);
		if (
			($ft && $ft->getLid($uid)) ||
			($ht && $ht->getLid($uid)) ||
			($ot && $ot->getLid($uid))
		) {
			$result[] = 'bestuur';
		}
		foreach (
			$this->groepLidRepository->findBy(['uid' => $uid])
			as $commissielid
		) {
			$commissie = $commissielid->groep;
			if (
				$commissie->status === GroepStatus::HT() or
				$commissie->status === GroepStatus::FT()
			) {
				$result[] = $commissie->familie;
			}
		}
		return $result;
	}
}
