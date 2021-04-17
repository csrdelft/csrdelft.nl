<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */
class LichtingLidRepository extends GroepLidRepository {
	/**
	 * Create LichtingLid on the fly.
	 *
	 * @param Lichting $lichting
	 * @param string $uid
	 * @return GroepLid|false
	 */
	public function get(Groep $lichting, $uid) {
		$profiel = ProfielRepository::get($uid);
		if ($profiel && $profiel->lidjaar === $lichting->lidjaar) {
			$lid = $this->nieuw($lichting, $uid);
			$lid->door_uid = null;
			$lid->door_profiel = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}
}
