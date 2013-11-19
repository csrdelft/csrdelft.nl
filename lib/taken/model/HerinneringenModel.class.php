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
		$lid = \LidCache::getLid($uid);
		if (!$lid instanceof \Lid) {
			throw new \Exception('Taak "'. $taak->getCorveeFunctie()->getNaam() .'" op '. $datum .' is nog niet toegewezen aan lid!'. (!empty($uid) ? ' $uid ='. $uid : ''));
		}
		//$to = $lid->getEmail();
		$to = $uid .'@csrdelft.nl';
		
		setlocale(LC_ALL, 'nl_NL');
		$headers = 'From: noreply@csrdelft.nl\r\n';
		$headers.= 'Reply-To: corvee@csrdelft.nl\r\n';
		$headers.= 'Content-Type: text/plain; charset=UTF-8\r\n';
		$headers.= 'X-Mailer: csrdelft.nl/PubCie\r\n';
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC') {
			$headers = str_replace('\r\n', '\r', $headers);
		}
		elseif (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			$headers = str_replace('\r\n', '\n', $headers);
		}
		
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
		
		if (mail($to, $onderwerp, $bericht, $headers)) { // succes
			TakenModel::updateGemaild($taak);
		}
		else {
			throw new \Exception('Corvee email faalt voor '. $lidnaam);
		}
	}
	
	public static function stuurHerinneringen() {
		$van = strtotime(date('Y-m-d'));
		$tot = strtotime('+5 weeks', $van);
		$taken = TakenModel::getTakenVoorAgenda($van, $tot, true);
		
		$teller = 0;
		$errors = array();
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					self::stuurHerinnering($taak);
					$teller++;
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
