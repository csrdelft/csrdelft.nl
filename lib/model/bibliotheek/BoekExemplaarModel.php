<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\profiel\Profiel;
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


	public function get($id) {
		return self::instance()->retrieveByPrimaryKey([$id]);
	}

	public static function getExemplaren(Boek $boek) {
		return self::instance()->find("boek_id = ?", [$boek->id]);
	}

	/**
	 * @param Profiel $profiel
	 * @return BoekExemplaar[]
	 */
	public static function getGeleend(Profiel $profiel) {
		return self::instance()->find("uitgeleend_uid = ?", [$profiel->uid])->fetchAll();
	}

	/**
	 * @param $uid
	 * @return BoekExemplaar[]
	 */
	public static function getEigendom($uid) {
		return self::instance()->find("eigenaar_uid = ?", [$uid])->fetchAll();
	}

	public static function leen(BoekExemplaar $exemplaar, string $uid) {
		if (!$exemplaar->kanLenen($uid)) {
			return false;
		} else {
			$exemplaar->status = 'uitgeleend';
			$exemplaar->uitgeleend_uid = $uid;
			self::instance()->update($exemplaar);
			return true;
		}
	}

	public static function addExemplaar(Boek $boek, string $uid) {
		$exemplaar = new BoekExemplaar();
		$exemplaar->boek_id = $boek->id;
		$exemplaar->eigenaar_uid = $uid;
		$exemplaar->toegevoegd = getDateTime();
		$exemplaar->uitleendatum= '0000-00-00 00:00:00';
		$exemplaar->opmerking = '';
		$exemplaar->leningen = 0;
		self::instance()->create($exemplaar);
	}

	public static function terugGegeven(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend()) {
			$exemplaar->status = 'teruggegeven';
			self::instance()->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public static function terugOntvangen(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven()) {
			$exemplaar->status = 'beschikbaar';
			self::instance()->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public static function setVermist(BoekExemplaar $exemplaar) {
		if ($exemplaar->isBeschikbaar()) {
			$exemplaar->status = 'vermist';
			self::instance()->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}

	public static function setGevonden(BoekExemplaar $exemplaar) {
		if ($exemplaar->isVermist()) {
			$exemplaar->status = 'beschikbaar';
			self::instance()->update($exemplaar);
			return true;
		} else {
			return false;
		}
	}
}
