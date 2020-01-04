<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\bibliotheek\BoekExemplaar;

/**
 * RecensieModel.class.php  |  Gerrit Uitslag
 *
 * een boekbeschrijving of boekrecensie
 *
 */
class BoekExemplaarModel extends PersistenceModel {


	const ORM = BoekExemplaar::class;


	/**
	 * @param $id
	 * @return PersistentEntity|false|BoekExemplaar
	 */
	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	public function getExemplaren(Boek $boek) {
		return $this->find("boek_id = ?", [$boek->id]);
	}

	/**
	 * @param Profiel $profiel
	 * @return BoekExemplaar[]
	 */
	public function getGeleend(Profiel $profiel) {
		return $this->find("uitgeleend_uid = ?", [$profiel->uid])->fetchAll();
	}

	/**
	 * @param $uid
	 * @return BoekExemplaar[]
	 */
	public function getEigendom($uid) {
		return $this->find("eigenaar_uid = ?", [$uid])->fetchAll();
	}

	public function leen(BoekExemplaar $exemplaar, string $uid) {
		if (!$exemplaar->kanLenen($uid)) {
			return false;
		} else {
			$exemplaar->status = 'uitgeleend';
			$exemplaar->uitgeleend_uid = $uid;
			$this->update($exemplaar);
			return true;
		}
	}

	public function addExemplaar(Boek $boek, string $uid) {
		$exemplaar = new BoekExemplaar();
		$exemplaar->boek_id = $boek->id;
		$exemplaar->eigenaar_uid = $uid;
		$exemplaar->toegevoegd = getDateTime();
		$exemplaar->uitleendatum= '0000-00-00 00:00:00';
		$exemplaar->opmerking = '';
		$exemplaar->leningen = 0;
		$this->create($exemplaar);
	}

	public function terugGegeven(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend()) {
			$exemplaar->status = 'teruggegeven';
			$this->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public function terugOntvangen(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven()) {
			$exemplaar->status = 'beschikbaar';
			$this->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public function setVermist(BoekExemplaar $exemplaar) {
		if ($exemplaar->isBeschikbaar()) {
			$exemplaar->status = 'vermist';
			$this->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public function setGevonden(BoekExemplaar $exemplaar) {
		if ($exemplaar->isVermist()) {
			$exemplaar->status = 'beschikbaar';
			$this->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}
}
