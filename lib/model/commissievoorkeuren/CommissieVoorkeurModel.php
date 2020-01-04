<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurVoorkeur;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\Orm\PersistenceModel;
use PDOStatement;

class CommissieVoorkeurModel extends PersistenceModel {

	const ORM = VoorkeurVoorkeur::class;


	/**
	 * @param Profiel $profiel
	 * @return VoorkeurVoorkeur[]|false
	 */
	public function getVoorkeurenVoorLid(Profiel $profiel): PDOStatement {
		return $this->find("uid = ?", array($profiel->uid));
	}

	/**
	 * @param VoorkeurCommissie $commissie
	 * @return VoorkeurVoorkeur[]|false
	 */
	public function getVoorkeurenVoorCommissie(VoorkeurCommissie $commissie, int $minVoorkeurWaarde = 1): PDOStatement {
		return $this->find("cid = ? and voorkeur >= ?", array($commissie->id, $minVoorkeurWaarde));
	}

	/**
	 * @param Profiel $profiel
	 * @param VoorkeurCommissie $commissie
	 * @return VoorkeurVoorkeur|false
	 */
	public function getVoorkeur(Profiel $profiel, VoorkeurCommissie $commissie): VoorkeurVoorkeur {
		$voorkeur = $this->retrieveByPrimaryKey([$profiel->uid, $commissie->id]);
		if ($voorkeur == null) {
			$voorkeur = new VoorkeurVoorkeur();
			$voorkeur->uid = $profiel->uid;
			$voorkeur->cid = $commissie->id;
			$voorkeur->voorkeur = 1;
		}
		return $voorkeur;
	}

}
