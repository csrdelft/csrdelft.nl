<?php

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\entity\groepen\LichtingsLid;
use CsrDelft\model\ProfielModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */
class LichtingLedenModel extends AbstractGroepLedenModel {

	const ORM = LichtingsLid::class;

	/**
	 * Create LichtingLid on the fly.
	 *
	 * @param Lichting $lichting
	 * @param string $uid
	 * @return LichtingsLid|false
	 */
	public static function get(AbstractGroep $lichting, $uid) {
		$profiel = ProfielModel::get($uid);
		if ($profiel AND $profiel->lidjaar === $lichting->lidjaar) {
			$lid = static::instance()->nieuw($lichting, $uid);
			$lid->door_uid = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}

	/**
	 * Return leden van lichting.
	 *
	 * @param Lichting $lichting
	 * @return LichtingsLid[]
	 */
	public function getLedenVoorGroep(AbstractGroep $lichting) {
		$leden = array();
		foreach (ProfielModel::instance()->prefetch('lidjaar = ?', array($lichting->lidjaar)) as $profiel) {
			$lid = static::get($lichting, $profiel->uid);
			if ($lid) {
				$leden[] = $lid;
			}
		}
		return $leden;
	}
}
