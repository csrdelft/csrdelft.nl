<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\groepen\leden\CommissieLedenRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

class RechtenGroepenRepository extends AbstractGroepenRepository {
	/** @var BesturenRepository */
	private $besturenModel;
	/** @var CommissieLedenRepository */
	private $commissieLedenModel;
	/** @var CommissiesRepository */
	private $commissiesModel;

	public function __construct(BesturenRepository $besturenModel, CommissiesRepository $commissiesModel, CommissieLedenRepository $commissieLedenModel, AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, RechtenGroep::class);

		$this->besturenModel = $besturenModel;
		$this->commissiesModel = $commissiesModel;
		$this->commissieLedenModel = $commissieLedenModel;
	}

	public function nieuw($soort = null) {
		/** @var RechtenGroep $groep */
		$groep = parent::nieuw();
		$groep->rechten_aanmelden = P_LEDEN_MOD;
		return $groep;
	}

	public static function getNaam() {
		return 'overig';
	}

	/**
	 * Groepen waarvan de gevraagde gebruiker de wikipagina's mag lezen en bewerken.
	 *
	 * @param string $uid
	 * @return array
	 */
	public function getWikiToegang($uid) {
		$result = [];
		$profiel = ProfielRepository::get($uid);
		if (!$profiel) {
			return $result;
		}
		if ($profiel->isLid() OR $profiel->isOudlid()) {
			$result[] = 'htleden-oudleden';
		}
		// 1 generatie vooruit en 1 achteruit (default order by)
		$ft = $this->besturenModel->findOneBy(['status' => GroepStatus::FT()]);
		$ht = $this->besturenModel->findOneBy(['status' => GroepStatus::HT()]);
		$ot = $this->besturenModel->findOneBy(['status' => GroepStatus::OT()]);
		if (($ft && $ft->getLid($uid)) || ($ht && $ht->getLid($uid)) || ($ot && $ot->getLid($uid))) {
			$result[] = 'bestuur';
		}
		foreach ($this->commissieLedenModel->findBy(['uid' => $uid]) as $commissielid) {
			$commissie = $this->commissiesModel->get($commissielid->groep_id);
			if ($commissie->status === GroepStatus::HT OR $commissie->status === GroepStatus::FT) {
				$result[] = $commissie->familie;
			}
		}
		return $result;
	}

}
