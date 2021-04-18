<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\groepen\VerticaleLid;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */
class VerticaleLidRepository extends GroepLidRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, VerticaleLid::class);
	}

	/**
	 * Create VerticaleLid on the fly.
	 *
	 * @param Verticale $verticale
	 * @param string $uid
	 * @return VerticaleLid|false
	 */
	public function get(Groep $verticale, $uid) {
		$profiel = ProfielRepository::get($uid);
		if ($profiel AND $profiel->verticale === $verticale->letter) {
			$lid = $this->nieuw($verticale, $uid);
			if ($profiel->verticaleleider) {
				$lid->opmerking = 'Leider';
			} elseif ($profiel->kringcoach) {
				$lid->opmerking = 'Kringcoach';
			}
			$lid->door_uid = null;
			$lid->door_profiel = null;
			$lid->lidSinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}

}
