<?php

namespace CsrDelft\model;

use CsrDelft\LDAP;
use CsrDelft\model\bibliotheek\BiebCatalogus;
use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\OntvangtContactueel;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\entity\security\AccessRole;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use function CsrDelft\getDateTime;
use function CsrDelft\setMelding;
use Exception;


/**
 * ProfielModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProfielModel extends CachedPersistenceModel {

	const ORM = Profiel::class;

	protected static $instance;

	/**
	 * @param string $uid
	 * @return Profiel|false
	 */
	public static function get($uid) {
		$profiel = static::instance()->retrieveByPrimaryKey(array($uid));
		if (!$profiel) {
			return false;
		}
		return static::instance()->cache($profiel, true);
	}

	public static function getNaam($uid, $vorm) {
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getNaam($vorm);
	}

	public static function getLink($uid, $vorm) {
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getLink($vorm);
	}

	public static function existsUid($uid) {
		return static::instance()->existsByPrimaryKey(array($uid));
	}

	public static function existsNick($nick) {
		return Database::instance()->sqlExists(static::instance()->getTableName(), 'nickname = ?', array($nick));
	}

	public static function existsDuck($duck) {
		return Database::instance()->sqlExists(static::instance()->getTableName(), 'duckname = ?', array($duck));
	}

	public function nieuw($lidjaar, $lidstatus) {
		$profiel = new Profiel();
		$profiel->lidjaar = $lidjaar;
		$profiel->status = $lidstatus;
        $profiel->ontvangtcontactueel = OntvangtContactueel::Nee;
		$profiel->changelog = '[div]Aangemaakt als ' . LidStatus::getDescription($profiel->status) . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $profiel;
	}

	/**
	 * @param PersistentEntity|Profiel $profiel
	 * @return string
	 */
	public function create(PersistentEntity $profiel) {
		// Lichting zijn de laatste 2 cijfers van lidjaar
		$jj = substr($profiel->lidjaar, 2, 2);
		$laatste_uid = Database::instance()->sqlSelect(array('MAX(uid)'), $this->getTableName(), 'LEFT(uid, 2) = ?', array($jj), null, null, 1)->fetchColumn();
		if ($laatste_uid) {
			// Volgnummer zijn de laatste 2 cijfers van uid
			$volgnummer = intval(substr($laatste_uid, 2, 2)) + 1;
		} else {
			$volgnummer = 1;
		}
		$profiel->uid = $jj . sprintf('%02d', $volgnummer);
		return parent::create($profiel);
	}

	/**
	 * @param PersistentEntity|Profiel $profiel
	 * @return int
	 */
	public function update(PersistentEntity $profiel) {
		try {
			$this->save_ldap($profiel);
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1); //TODO: logging
		}
		$this->cache($profiel, true, true);
		return parent::update($profiel);
	}

	/**
	 * Sla huidige objectstatus op in LDAP.
	 *
	 * @param Profiel $profiel
	 * @param LDAP $ldap persistent connection
	 * @return bool success
	 */
	public function save_ldap(Profiel $profiel, LDAP $ldap = null) {
		$success = true;

		if ($ldap === null) {
			$ldap = new LDAP();
			$persistent = false;
		} else {
			$persistent = true;
		}

		// Alleen leden, gastleden, novieten en kringels staan in LDAP (en Knorrie öO~ en Gerrit Uitslag)
		if (preg_match('/^S_(LID|GASTLID|NOVIET|KRINGEL|CIE)$/', $profiel->status) or $profiel->uid == '9808' or $profiel->uid == '0431') {

			// LDAP entry in elkaar zetten
			$entry = array();
			$entry['uid'] = $profiel->uid;
			$entry['givenname'] = $profiel->voornaam;
			$entry['sn'] = $profiel->achternaam;
			if (substr($entry['uid'], 0, 2) == 'x2') {
				$entry['cn'] = $entry['sn'];
			} else {
				$entry['cn'] = $profiel->getNaam();
			}
			$entry['mail'] = $profiel->getPrimaryEmail();
			$entry['homephone'] = $profiel->telefoon;
			$entry['mobile'] = $profiel->mobiel;
			$entry['homepostaladdress'] = implode('', array($profiel->adres, $profiel->postcode, $profiel->woonplaats));
			$entry['o'] = 'C.S.R. Delft';
			$entry['mozillanickname'] = $profiel->nickname;
			$entry['mozillausehtmlmail'] = 'FALSE';
			$entry['mozillahomestreet'] = $profiel->adres;
			$entry['mozillahomelocalityname'] = $profiel->woonplaats;
			$entry['mozillahomepostalcode'] = $profiel->postcode;
			$entry['mozillahomecountryname'] = $profiel->land;
			$entry['mozillahomeurl'] = $profiel->website;
			$entry['description'] = 'Ledenlijst C.S.R. Delft';
			$entry['userPassword'] = $profiel->getAccount()->pass_hash;

			$woonoord = $profiel->getWoonoord();
			if ($woonoord) {
				$entry['ou'] = $woonoord->naam;
			}

			# lege velden er uit gooien
			foreach ($entry as $i => $e) {
				if ($e == '') {
					unset($entry[$i]);
				}
			}

			// Bestaat deze uid al in LDAP? dan wijzigen, anders aanmaken
			if ($ldap->isLid($entry['uid'])) {
				$success = $ldap->modifyLid($entry['uid'], $entry);
			} else {
				$success = $ldap->addLid($entry['uid'], $entry);
			}
		} else {
			// Als het een andere status is even kijken of de uid in LDAP voorkomt, zo ja wissen
			if ($ldap->isLid($profiel->uid)) {
				$success = $ldap->removeLid($profiel->uid);
			}
		}

		if (!$persistent) {
			$ldap->disconnect();
		}

		return $success;
	}

	public function wijzig_lidstatus(Profiel $profiel, $oudestatus) {
		$changelog = '';
		// Maaltijd en corvee bijwerken
		$geenAboEnCorveeVoor = array(LidStatus::Oudlid, LidStatus::Erelid, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Commissie, LidStatus::Overleden);
		if (in_array($profiel->status, $geenAboEnCorveeVoor)) {
			//maaltijdabo's uitzetten (R_ETER is een S_NOBODY die toch een abo mag hebben)
			$account = AccountModel::get($profiel->uid);
			if (!$account OR $account->perm_role !== AccessRole::Eter) {
				$removedabos = $this->disableMaaltijdabos($profiel, $oudestatus);
				if ($removedabos != '') {
					$changelog .= $removedabos;
				}
			}
			// Toekomstige corveetaken verwijderen
			$removedcorvee = $this->removeToekomstigeCorvee($profiel, $oudestatus);
			if ($removedcorvee != '') {
				$changelog .= $removedcorvee;
			}
		}
		// Mailen naar fisci,bibliothecaris...
		$wordtinactief = array(LidStatus::Oudlid, LidStatus::Erelid, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden);
		$wasactief = array(LidStatus::Noviet, LidStatus::Gastlid, LidStatus::Lid, LidStatus::Kringel);
		if (in_array($profiel->status, $wordtinactief) AND in_array($oudestatus, $wasactief)) {
			$this->notifyFisci($profiel, $oudestatus);
			$this->notifyBibliothecaris($profiel, $oudestatus);
		}
		return $changelog;
	}

	/**
	 * Zet alle abo's uit en geeft een changelog-regel terug.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return string changelogregel
	 */
	private function disableMaaltijdabos(Profiel $profiel, $oudestatus) {
				$aantal = MaaltijdAbonnementenModel::instance()->verwijderAbonnementenVoorLid($profiel->uid);
		if ($aantal > 0) {
			return 'Afmelden abo\'s: ' . $aantal . ' uitgezet.[br]';
		}
		return null;
	}

	/**
	 * Verwijder toekomstige corveetaken en geef changelog-regel terug.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return string changelogregel
	 */
	private function removeToekomstigeCorvee(Profiel $profiel, $oudestatus) {
		$taken = CorveeTakenModel::instance()->getKomendeTakenVoorLid($profiel->uid);
		$aantal = CorveeTakenModel::instance()->verwijderTakenVoorLid($profiel->uid);
		if (sizeof($taken) !== $aantal) {
			setMelding('Niet alle toekomstige corveetaken zijn verwijderd!', -1);
		}
		$changelog = null;
		if ($aantal > 0) {
			$changelog = 'Verwijderde corveetaken:[br]';
			foreach ($taken as $taak) {
				$changelog .= strftime('%a %e-%m-%Y', $taak->getBeginMoment()) . ' ' . $taak->getCorveeFunctie()->naam . '[br]';
			}
			// Corveeceasar mailen over vrijvallende corveetaken.
			$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'mail/toekomstigcorveeverwijderd.mail');
			$values = array(
				'AANTAL' => $aantal,
				'NAAM'	 => ProfielModel::getNaam($profiel->uid, 'volledig'),
				'UID'	 => $profiel->uid,
				'OUD'	 => $oudestatus,
				'NIEUW'	 => $profiel->status,
				'CHANGE' => str_replace('[br]', "\n", $changelog),
				'ADMIN'	 => LoginModel::getProfiel()->getNaam()
			);
			$mail = new Mail(array('corvee@csrdelft.nl' => 'CorveeCaesar'), 'Lid-af: toekomstig corvee verwijderd', $bericht);
			$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
			$mail->setPlaceholders($values);
			$mail->send();
		}
		return $changelog;
	}

	/**
	 * Mail naar fisci over statuswijzigingen. Kunnen zij hun systemen weer mee updaten.
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyFisci(Profiel $profiel, $oudestatus) {
		// Saldi ophalen
		$saldi = '';
		$saldi .= 'CiviSaldo: ' . $profiel->getCiviSaldo() . "\n";

		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'mail/lidafmeldingfisci.mail');
		$values = array(
			'NAAM'	 => ProfielModel::getNaam($profiel->uid, 'volledig'),
			'UID'	 => $profiel->uid,
			'OUD'	 => $oudestatus,
			'NIEUW'	 => $profiel->status,
			'SALDI'	 => $saldi,
			'ADMIN'	 => LoginModel::getProfiel()->getNaam()
		);
		$to = array(
			'fiscus@csrdelft.nl'		 => 'Fiscus C.S.R.',
			'maalcie-fiscus@csrdelft.nl' => 'MaalCie fiscus C.S.R.',
			'soccie@csrdelft.nl'		 => 'SocCie C.S.R.'
		);

		$mail = new Mail($to, 'Melding lid-af worden', $bericht);
		$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
		$mail->setPlaceholders($values);

		return $mail->send();
	}

	/**
	 * Mail naar bibliothecaris en leden over geleende boeken
	 *
	 * @param Profiel $profiel
	 * @param $oudestatus
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyBibliothecaris(Profiel $profiel, $oudestatus) {
				$boeken = BiebCatalogus::getBoekenByUid($profiel->uid, 'geleend');
		if (!is_array($boeken)) {
			$boeken = array();
		}
		// Lijst van boeken genereren
		$bknleden = $bkncsr = array(
			'kopje'	 => '',
			'lijst'	 => '',
			'aantal' => 0
		);
		foreach ($boeken as $boek) {
			if ($boek['eigenaar_uid'] == 'x222') {
				$bkncsr['aantal'] ++;
				$bkncsr['lijst'] .= "{$boek['titel']} door {$boek['auteur']}\n";
				$bkncsr['lijst'] .= " - " . CSR_ROOT . "/bibliotheek/boek/{$boek['id']}\n";
			} else {
				$bknleden['aantal'] ++;
				$bknleden['lijst'] .= "{$boek['titel']} door {$boek['auteur']}\n";
				$bknleden['lijst'] .= " - " . CSR_ROOT . "/bibliotheek/boek/{$boek['id']}\n";
				$naam = ProfielModel::getNaam($boek['eigenaar_uid'], 'volledig');
				$bknleden['lijst'] .= " - boek is geleend van: $naam\n";
			}
		}
		// Kopjes
		$mv = ($profiel->geslacht == Geslacht::Man ? 'hem' : 'haar');
		$enkelvoud = "Het volgende boek is nog door {$mv} geleend";
		$meervoud = "De volgende boeken zijn nog door {$mv} geleend";
		if ($bkncsr['aantal'])
			$bkncsr['kopje'] = ($bkncsr['aantal'] > 1 ? $meervoud : $enkelvoud) . " van de C.S.R.-bibliotheek:";
		if ($bknleden['aantal'])
			$bknleden['kopje'] = ($bknleden['aantal'] > 1 ? $meervoud : $enkelvoud) . " van leden:";

		// Alleen mailen als er C.S.R.boeken zijn
		if ($bkncsr['aantal'] == 0) {
			return false;
		}

		$to = array(
			'bibliothecaris@csrdelft.nl' => 'Bibliothecaris C.S.R.',
			$profiel->getPrimaryEmail()	 => $profiel->getNaam('civitas')
		);
		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'mail/lidafgeleendebiebboeken.mail');
		$values = array(
			'NAAM'		 => ProfielModel::getNaam($profiel->uid, 'volledig'),
			'UID'		 => $profiel->uid,
			'OUD'		 => substr($oudestatus, 2),
			'NIEUW'		 => ($profiel->status === LidStatus::Nobody ? 'GEEN LID' : substr($profiel->status, 2)),
			'CSRLIJST'	 => $bkncsr['kopje'] . "\n" . $bkncsr['lijst'],
			'LEDENLIJST' => ($bkncsr['aantal'] > 0 ? "Verder ter informatie: " . $bknleden['kopje'] . "\n" . $bknleden['lijst'] : ''),
			'ADMIN'		 => LoginModel::getProfiel()->getNaam()
		);
		$mail = new Mail($to, 'Geleende boeken - Melding lid-af worden', $bericht);
		$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
		$mail->setPlaceholders($values);

		return $mail->send();
	}

}
