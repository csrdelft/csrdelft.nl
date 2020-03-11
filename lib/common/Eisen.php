<?php


namespace CsrDelft\common;


use CsrDelft\model\security\LoginModel;
use Doctrine\Common\Collections\Criteria;

/**
 * Criteria die vaak voorkomen in de stek.
 *
 * @package CsrDelft\common
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class Eisen {
	/**
	 * Filter een PersistentCollection op de uid van de huidige gebruiker. Gaat er vanuit dat er dan maar 1 resultaat over is.
	 *
	 * @param string $veld
	 * @return Criteria
	 */
	public static function voorIngelogdeGebruiker($veld = 'uid') {
		return self::voorGebruiker(LoginModel::getUid(), $veld);
	}

	public static function voorGebruiker($uid, $veld = 'uid') {
		return Criteria::create()->where(Criteria::expr()->eq($veld, $uid))->setMaxResults(1);
	}
}
