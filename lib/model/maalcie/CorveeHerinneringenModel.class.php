<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\ProfielModel;

/**
 * CorveeHerinneringenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class CorveeHerinneringenModel {

	public static function stuurHerinnering(CorveeTaak $taak) {
		$datum = date('d-m-Y', strtotime($taak->datum));
		$uid = $taak->uid;
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			throw new CsrGebruikerException($datum . ' ' . $taak->getCorveeFunctie()->naam . ' niet toegewezen!' . (!empty($uid) ? ' ($uid =' . $uid . ')' : ''));
		}
		$lidnaam = ProfielModel::getNaam($uid, 'civitas');
		$to = array($profiel->getPrimaryEmail() => $lidnaam);
		$from = 'corvee@csrdelft.nl';
		$onderwerp = 'C.S.R. Delft corvee ' . $datum;
		$bericht = $taak->getCorveeFunctie()->email_bericht;
		$eten = '';
		if ($taak->maaltijd_id !== null) {
			$aangemeld = MaaltijdAanmeldingenModel::instance()->getIsAangemeld($taak->maaltijd_id, $uid);
			if ($aangemeld) {
				$eten = InstellingenModel::get('corvee', 'mail_wel_meeeten');
			} else {
				$eten = InstellingenModel::get('corvee', 'mail_niet_meeeten');
			}
		}
		$mail = new Mail($to, $onderwerp, $bericht);
		$mail->setFrom($from);
		$mail->setPlaceholders(array('LIDNAAM' => $lidnaam, 'DATUM' => $datum, 'MEEETEN' => $eten));
		if ($mail->send()) { // false if failed
			if (!$mail->inDebugMode()) {
				CorveeTakenModel::instance()->updateGemaild($taak);
			}
			return $datum . ' ' . $taak->getCorveeFunctie()->naam . ' verstuurd! (' . $lidnaam . ')';
		} else {
			throw new CsrGebruikerException($datum . ' ' . $taak->getCorveeFunctie()->naam . ' faalt! (' . $lidnaam . ')');
		}
	}

	public static function stuurHerinneringen() {
		$vooraf = str_replace('-', '+', InstellingenModel::get('corvee', 'herinnering_1e_mail'));
		$van = strtotime(date('Y-m-d'));
		$tot = strtotime($vooraf, $van);
		$taken = CorveeTakenModel::instance()->getTakenVoorAgenda($van, $tot, true);
		$verzonden = array();
		$errors = array();
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					$verzonden[] = self::stuurHerinnering($taak);
				} catch (CsrGebruikerException $e) {
					$errors[] = $e;
				}
			}
		}
		return array($verzonden, $errors);
	}

}
