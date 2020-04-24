<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\repository\ProfielRepository;

/**
 * CorveeToewijzenModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class CorveeToewijzenService {
	/**
	 * @var CorveePuntenService
	 */
	private $corveePuntenService;
	/**
	 * @var CorveeVrijstellingenModel
	 */
	private $corveeVrijstellingenModel;

	public function __construct(CorveeVrijstellingenModel $corveeVrijstellingenModel, CorveePuntenService $corveePuntenService) {
		$this->corveePuntenService = $corveePuntenService;
		$this->corveeVrijstellingenModel = $corveeVrijstellingenModel;
	}

	/**
	 * Bepaald de suggesties voor het toewijzen van een corveetaak.
	 * Als er een kwalificatie benodigd is worden alleen de
	 * gekwalificeerde leden teruggegeven.
	 *
	 * @param CorveeTaak $taak
	 * @return array
	 * @throws CsrGebruikerException
	 */
	public function getSuggesties(CorveeTaak $taak) {
		$vrijstellingen = $this->corveeVrijstellingenModel->getAlleVrijstellingen(true); // grouped by uid
		$functie = $taak->getCorveeFunctie();
		if ($functie->kwalificatie_benodigd) { // laad alleen gekwalificeerde leden
			$lijst = array();
			$avg = 0;
			foreach ($functie->getKwalificaties() as $kwali) {
				$uid = $kwali->uid;
				$profiel = $kwali->profiel;
				if (!$profiel) {
					throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
				}
				if (!$profiel->isLid()) {
					continue; // geen oud-lid of overleden lid
				}
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->begin_datum) && $datum <= strtotime($vrijstelling->eind_datum)) {
						continue; // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
				}
				$lijst[$uid] = $this->corveePuntenService->loadPuntenVoorLid($profiel, array($functie->functie_id => $functie));
				$lijst[$uid]['aantal'] = $lijst[$uid]['aantal'][$functie->functie_id];
				$avg += $lijst[$uid]['aantal'];
			}
			$avg /= sizeof($lijst);
			foreach ($lijst as $uid => $punten) {
				$lijst[$uid]['relatief'] = $lijst[$uid]['aantal'] - (int)$avg;
			}
			$sorteer = 'sorteerKwali';
		} else {
			$lijst = $this->corveePuntenService->loadPuntenVoorAlleLeden();
			foreach ($lijst as $uid => $punten) {
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->begin_datum) && $datum <= strtotime($vrijstelling->eind_datum)) {
						unset($lijst[$uid]); // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
					// corrigeer prognose in suggestielijst vóór de aanvang van de vrijstellingsperiode
					if ($vrijstelling !== null && $datum < strtotime($vrijstelling->begin_datum)) {
						$lijst[$uid]['prognose'] -= $vrijstelling->getPunten();
					}
				}
			}
			$sorteer = 'sorteerPrognose';
		}
		foreach ($lijst as $uid => $punten) {
			$lijst[$uid]['laatste'] = CorveeTakenModel::instance()->getLaatsteTaakVanLid($uid);
			if ($lijst[$uid]['laatste'] !== false && $lijst[$uid]['laatste']->getBeginMoment() >= strtotime(instelling('corvee', 'suggesties_recent_verbergen'), $taak->getBeginMoment())) {
				$lijst[$uid]['recent'] = true;
			} else {
				$lijst[$uid]['recent'] = false;
			}
			if ($taak->crv_repetitie_id !== null) {
				$lijst[$uid]['voorkeur'] = CorveeVoorkeurenModel::instance()->getHeeftVoorkeur($taak->crv_repetitie_id, $uid);
			} else {
				$lijst[$uid]['voorkeur'] = false;
			}
		}
		uasort($lijst, [$this, $sorteer]);
		return $lijst;
	}

	function sorteerKwali($a, $b) {
		if ($a['laatste'] !== false && $b['laatste'] !== false) {
			$a = $a['laatste']->getBeginMoment();
			$b = $b['laatste']->getBeginMoment();
		} elseif ($a['laatste'] === false) {
			return -1;
		} elseif ($b['laatste'] === false) {
			return 1;
		} else {
			$a = $a['aantal'];
			$b = $b['aantal'];
		}
		if ($a === $b) {
			return 0;
		} elseif ($a < $b) { // < ASC
			return -1;
		} else {
			return 1;
		}
	}

	function sorteerPrognose($a, $b) {
		$a = $a['prognose'];
		$b = $b['prognose'];
		if ($a === $b) {
			return 0;
		} elseif ($a < $b) { // < ASC
			return -1;
		} else {
			return 1;
		}
	}
}
