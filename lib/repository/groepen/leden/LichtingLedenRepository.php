<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\entity\groepen\LichtingsLid;
use CsrDelft\repository\AbstractGroepLedenRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */
class LichtingLedenRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, LichtingsLid::class);
	}

	/**
	 * Create LichtingLid on the fly.
	 *
	 * @param Lichting $lichting
	 * @param string $uid
	 * @return LichtingsLid|false
	 */
	public function get(AbstractGroep $lichting, $uid) {
		$profiel = ProfielRepository::get($uid);
		if ($profiel AND $profiel->lidjaar === $lichting->lidjaar) {
			$lid = $this->nieuw($lichting, $uid);
			$lid->door_uid = null;
			$lid->door_profiel = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}
}
