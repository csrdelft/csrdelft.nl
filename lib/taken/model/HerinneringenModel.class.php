<?php


require_once 'mail.class.php';

/**
 * HerinneringenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class HerinneringenModel {

	public static function stuurHerinnering(CorveeTaak $taak) {
		$datum = date('d-m-Y', strtotime($taak->getDatum()));
		$uid = $taak->getLidId();
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new Exception($datum .' '. $taak->getCorveeFunctie()->getNaam() .' niet toegewezen!'. (!empty($uid) ? ' ($uid ='. $uid .')' : ''));
		}
		//$to = $lid->getEmail();
		$to = $uid .'@csrdelft.nl';
		$from = 'corvee@csrdelft.nl';
		$onderwerp = 'C.S.R. Delft Corvee - '. $datum;
		$bericht = $taak->getCorveeFunctie()->getEmailBericht();
		$lidnaam = $lid->getNaamLink('civitas');
		$eten = '';
		if ($taak->getMaaltijdId() !== null) {
			$aangemeld = AanmeldingenModel::getIsAangemeld($taak->getMaaltijdId(), $uid);
			if ($aangemeld) {
				$eten = 'U eet WEL mee met de maaltijd.';
			}
			else {
				$eten = 'U eet NIET mee met de maaltijd.';
			}
		}
		$bericht = str_replace(array('LIDNAAM', 'DATUM', 'MEEETEN'), array($lidnaam, $datum, $eten), $bericht);
		$mail = new Mail($to, $onderwerp, $bericht);
		$mail->setFrom($from);
		if ($mail->send()) { // false if failed
			TakenModel::updateGemaild($taak);
			return $datum .' '. $taak->getCorveeFunctie()->getNaam() .' verstuurd! ('. $lidnaam .')';
		}
		else {
			throw new Exception($datum .' '. $taak->getCorveeFunctie()->getNaam() .' faalt! ('. $lidnaam .')');
		}
	}
	
	public static function stuurHerinneringen() {
		if (array_key_exists('herinnering_1e_mail', $GLOBALS['corvee'])) {
			$vooraf = str_replace('-', '+', $GLOBALS['corvee']['herinnering_1e_mail']);
		}
		$van = strtotime(date('Y-m-d'));
		$tot = strtotime($vooraf, $van);
		$taken = TakenModel::getTakenVoorAgenda($van, $tot, true);
		$verzonden = array();
		$errors = array();
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					$verzonden[] = self::stuurHerinnering($taak);
				}
				catch (\Exception $e) {
					$errors[] = $e;
				}
			}
		}
		return array($verzonden, $errors);
	}
}

?>