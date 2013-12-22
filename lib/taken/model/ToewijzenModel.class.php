<?php
namespace Taken\CRV;

require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/model/KwalificatiesModel.class.php';
require_once 'taken/model/PuntenModel.class.php';
require_once 'taken/model/VoorkeurenModel.class.php';

/**
 * ToewijzenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class ToewijzenModel {

	/**
	 * Bepaald de suggesties voor het toewijzen van een corveetaak.
	 * Als er een kwalificatie benodigd is worden alleen de
	 * gekwalificeerde leden teruggegeven.
	 * 
	 * @param \Taken\CRV\CorveeTaak $taak
	 * @return type
	 * @throws \Exception
	 */
	public static function getSuggesties(CorveeTaak $taak) {
		$vrijstellingen = VrijstellingenModel::getAlleVrijstellingen(true); // grouped by uid
		$functie = $taak->getCorveeFunctie();
		if ($functie->getIsKwalificatieBenodigd()) { // laad alleen gekwalificeerde leden
			$kwalificaties = KwalificatiesModel::getKwalificatiesVoorFunctie($functie);
			$lijst = array();
			foreach ($kwalificaties as $kwali) {
				$uid = $kwali->getLidId();
				$lid = \LidCache::getLid($uid); // false if lid does not exist
				if (!$lid instanceof \Lid) {
					throw new \Exception('Lid bestaat niet: $uid ='. $uid);
				}
				if (!$lid->isLid()) {
					continue; // geen oud-lid of overleden lid
				}
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->getBeginDatum()) && $datum <= strtotime($vrijstelling->getEindDatum())) {
						continue; // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
				}
				$lijst[$uid] = PuntenModel::loadPuntenVoorLid($lid, array($functie->getFunctieId() => $functie));
				$lijst[$uid]['aantal'] = $lijst[$uid]['aantal'][$functie->getFunctieId()];
			}
			$sorteer = 'sorteerAantal';
		}
		else {
			$lijst = PuntenModel::loadPuntenVoorAlleLeden();
			foreach ($lijst as $uid => $punten) {
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->getBeginDatum()) && $datum <= strtotime($vrijstelling->getEindDatum())) {
						unset($lijst[$uid]); // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
					// corrigeer prognose in suggestielijst vóór de aanvang van de vrijstellingsperiode
					if ($vrijstelling !== null && $datum < strtotime($vrijstelling->getBeginDatum())) {
						$lijst[$uid]['prognose'] -= $vrijstelling->getPunten();
					}
				}
			}
			$sorteer = 'sorteerPrognose';
		}
		uasort($lijst, array('self', $sorteer));
		foreach ($lijst as $uid => $punten) {
			$lijst[$uid]['laatste'] = TakenModel::getLaatsteTaakVanLid($uid);
			if ($lijst[$uid]['laatste'] !== null && $lijst[$uid]['laatste']->getBeginMoment() >= strtotime($GLOBALS['suggesties_recent_verbergen'])) {
				$lijst[$uid]['recent'] = true;
			}
			else {
				$lijst[$uid]['recent'] = false;
			}
			if ($taak->getCorveeRepetitieId() !== null) {
				$lijst[$uid]['voorkeur'] = VoorkeurenModel::getHeeftVoorkeur($taak->getCorveeRepetitieId(), $uid);
			}
		}
		return $lijst;
	}
	
	static function sorteerAantal($a, $b) {
		if ($a['aantal'] === $b['aantal']) {
			return 0;
		}
		elseif ($a['aantal'] < $b['aantal']) { // < ASC
			return -1;
		}
		else {
			return 1;
		}
	}
	
	static function sorteerPrognose($a, $b) {
		if ($a['prognose'] === $b['prognose']) {
			return 0;
		}
		elseif ($a['prognose'] < $b['prognose']) { // < ASC
			return -1;
		}
		else {
			return 1;
		}
	}
}

?>