<?php

namespace CsrDelft\model\groepen\leden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\groepen\VerticaleLid;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */
class VerticaleLedenModel extends AbstractGroepLedenModel {

	const ORM = VerticaleLid::class;

	/**
	 * Create VerticaleLid on the fly.
	 *
	 * @param Verticale $verticale
	 * @param string $uid
	 * @return VerticaleLid|false
	 */
	public function get(AbstractGroep $verticale, $uid) {
		$profiel = ProfielRepository::get($uid);
		if ($profiel AND $profiel->verticale === $verticale->letter) {
			$lid = $this->nieuw($verticale, $uid);
			if ($profiel->verticaleleider) {
				$lid->opmerking = 'Leider';
			} elseif ($profiel->kringcoach) {
				$lid->opmerking = 'Kringcoach';
			}
			$lid->door_uid = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}

	/**
	 * Return leden van verticale.
	 *
	 * @param Verticale $verticale
	 * @return VerticaleLid[]
	 */
	public function getLedenVoorGroep(AbstractGroep $verticale) {
		$leden = [];
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		/** @var Profiel $profielen */
		$profielen = $profielRepository->createQueryBuilder('p')
			->where('p.verticale := :verticale and p.status in (:lidstatus)')
			->setParameter('verticale', $verticale->letter)
			->setParameter('lidstatus', LidStatus::getLidLike())
			->getQuery()->getResult();
		foreach ($profielen as $profiel) {
			$lid = $this->get($verticale, $profiel->uid);
			if ($lid) {
				$leden[] = $lid;
			}
		}
		return $leden;
	}

}
