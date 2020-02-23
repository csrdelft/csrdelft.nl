<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\RechtenGroep;
use CsrDelft\model\groepen\leden\CommissieLedenModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\model\security\AccessModel;

class RechtenGroepenModel extends AbstractGroepenModel {
	/** @var BesturenModel */
	private $besturenModel;
	/** @var CommissieLedenModel */
	private $commissieLedenModel;
	/** @var CommissiesModel */
	private $commissiesModel;

	public function __construct(BesturenModel $besturenModel, CommissiesModel $commissiesModel, CommissieLedenModel $commissieLedenModel, AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);

		$this->besturenModel = $besturenModel;
		$this->commissiesModel = $commissiesModel;
		$this->commissieLedenModel = $commissieLedenModel;
	}

	const ORM = RechtenGroep::class;

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
		$ft = $this->besturenModel->find('status = ?', [GroepStatus::FT], null, null, 1)->fetch();
		$ht = $this->besturenModel->find('status = ?', [GroepStatus::HT], null, null, 1)->fetch();
		$ot = $this->besturenModel->find('status = ?', [GroepStatus::OT], null, null, 1)->fetch();
		if (($ft AND $ft->getLid($uid)) OR ($ht AND $ht->getLid($uid)) OR ($ot AND $ot->getLid($uid))) {
			$result[] = 'bestuur';
		}
		foreach ($this->commissieLedenModel->prefetch('uid = ?', array($uid)) as $commissielid) {
			$commissie = $this->commissiesModel->get($commissielid->groep_id);
			if ($commissie->status === GroepStatus::HT OR $commissie->status === GroepStatus::FT) {
				$result[] = $commissie->familie;
			}
		}
		return $result;
	}

}
