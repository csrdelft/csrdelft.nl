<?php
namespace Taken\CRV;
/**
 * EmailsModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class HerinneringenModel {

	public static function stuurHerinnering(CorveeTaak $taak) {
		$datum = date('d-m-Y', strtotime($taak->getDatum()));
		$uid = $taak->getLidId();
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception($datum .' '. $taak->getCorveeFunctie()->getNaam() .' niet toegewezen!'. (!empty($uid) ? ' ($uid ='. $uid .')' : ''));
		}
		//$to = $lid->getEmail();
		$to = $uid .'@csrdelft.nl';
		if ($_GET['DEBUG'] === 'TRUE') {
			$to = 'brussee@live.nl';
		}
		
		setlocale(LC_ALL, 'nl_NL');
		$headers = 'From: noreply@csrdelft.nl; \r\n';
		$headers.= 'Reply-To: corvee@csrdelft.nl; \r\n';
		$headers.= 'Content-Type: text/plain; charset=UTF-8; \r\n';
		$headers.= 'X-Mailer: csrdelft.nl/PubCie\r\n';
		
		$onderwerp = 'C.S.R. Delft Corvee - '. $datum;
		$bericht = $taak->getCorveeFunctie()->getEmailBericht();
		$lidnaam = $lid->getNaamLink('civitas');
		$eten = '';
		if ($taak->getMaaltijdId() !== null) {
			$aangemeld = \Taken\MLT\AanmeldingenModel::getIsAangemeld($taak->getMaaltijdId(), $uid);
			if ($aangemeld) {
				$eten = 'U eet WEL mee met de maaltijd.';
			}
			else {
				$eten = 'U eet NIET mee met de maaltijd.';
			}
		}
		$bericht = str_replace(array('LIDNAAM', 'DATUM', 'MEEETEN'), array($lidnaam, $datum, $eten), $bericht);
		
		if (mail($to, $onderwerp, $bericht, $headers)) { // false if failed
			TakenModel::updateGemaild($taak);
			return $datum .' '. $taak->getCorveeFunctie()->getNaam() .' verstuurd! ('. $lidnaam .')';
		}
		else {
			throw new \Exception($datum .' '. $taak->getCorveeFunctie()->getNaam() .' faalt! ('. $lidnaam .')');
		}
	}
	
	public static function stuurHerinneringen() {
		if (array_key_exists('herinnering_1e_mail', $GLOBALS)) {
			$vooraf = str_replace('-', '+', $GLOBALS['herinnering_1e_mail']);
		}
		$van = strtotime(date('Y-m-d'));
		$tot = strtotime($vooraf, $van);
		$taken = TakenModel::getTakenVoorAgenda($van, $tot, true);
		
		$teller = array();
		$errors = array();
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					$teller[] = self::stuurHerinnering($taak);
				}
				catch (\Exception $e) {
					$errors[] = $e;
				}
			}
		}
		return array($teller, $errors);
	}
}

?>
