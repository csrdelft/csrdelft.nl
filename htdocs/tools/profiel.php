<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

/**
 * Even wat uitleg over het toevoegen van nieuwe leden:
 * Door naar de url http://csrdelft.nl/profiel/2005/nieuw/Lid/ te gaan wordt er een
 * nieuw uid aangemaakt in het opgegeven jaar en status. Vervolgens wordt de browser meteen naar het
 * bewerken van het nieuwe profiel gestuurd, waar de gegevens van de noviet ingevoerd kunnen
 * worden. De code daarvoor is gelijk aan die van het bewerken van een bestaand profiel, met
 * een ander tekstje erboven. Ook worden de wachtwoordvelden en het bijnaamveld nog niet
 * weergeven.
 * 
 */
require_once 'configuratie.include.php';
require_once 'lid/profiel.class.php';

if (isset($_GET['uid'])) {
	$uid = $_GET['uid'];
} else {
	$uid = LoginModel::getUid();
}

//welke actie gaan we doen?
if (isset($_GET['a'])) {
	$actie = $_GET['a'];
	//is er een status opgegeven
	if (isset($_GET['s'])) {
		$status = $_GET['s'];
	} else {
		$status = null;
	}
} else {
	//default-actie.
	$actie = 'view';
}

if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {

	require_once 'model/CmsPaginaModel.class.php';
	require_once 'view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	require_once 'lid/profielcontent.class.php';
	require_once 'lid/profiel.class.php';

	switch ($actie) {

		case 'novietBewerken':
		case 'bewerken':
			$profiel = new ProfielBewerken($uid, $actie);

			if ($profiel->magBewerken()) {
				if ($profiel->isPosted()) {
					if ($profiel->validate()) {
						if ($profiel->save()) {
							setMelding('Opslaan geslaagd', 1);
							redirect(CSR_ROOT . '/profiel/' . $uid);
						}
						setMelding('Opslaan mislukt', -1);
					} else {
						setMelding('Invoer is niet volledig correct', -1);
					}
				}
				$midden = new ProfielEditContent($profiel, $actie);
				break;
			}
			$midden = new ProfielContent(LidCache::getLid($uid));
			break;

		case 'nieuw':
			//maak van een standaard statusstring van de input
			$status = 'S_' . strtoupper($status);
			if (!
					(LoginModel::mag('P_ADMIN,P_LEDEN_MOD') OR ( $status == 'S_NOVIET' AND LoginModel::mag('groep:novcie')))
			) {

				// nieuwe leden mogen worden aangemaakt door P_ADMIN,P_LEDEN_MOD,
				// novieten ook door de novcie.
				setMelding('U mag geen nieuwe leden aanmaken', -1);
				redirect(CSR_ROOT . '/profiel/');
			}
			try {
				//maak het nieuwe uid aan.
				$nieuwUid = Lid::createNew($_GET['uid'], $status);

				if ($status == 'S_NOVIET') {
					$bewerkactie = 'novietBewerken';
				} else {
					$bewerkactie = 'bewerken';
				}
				redirect(CSR_ROOT . '/profiel/' . $nieuwUid . '/' . $bewerkactie);
			} catch (Exception $e) {
				setMelding('<h3>Nieuw lidnummer aanmaken mislukt.</h3>' . $e->getMessage(), -1);
				redirect(CSR_ROOT . '/profiel/');
			}
			break;

		case 'wijzigstatus':
			if (!LoginModel::mag('P_ADMIN,P_LEDEN_MOD')) {
				setMelding('U mag lidstatus niet aanpassen', -1);
				redirect(CSR_ROOT . '/profiel/');
			}
			$profiel = new ProfielStatus($uid, $actie);

			if ($profiel->validate() AND $profiel->save()) {
				redirect(CSR_ROOT . '/profiel/' . $uid);
			} else {
				$midden = new ProfielStatusContent($profiel, $actie);
			}
			break;

		case 'voorkeuren':
			//TODO Rechten goed zetten!
			$voorkeur = new ProfielVoorkeur($uid, $actie);

			if ($voorkeur->magBewerken()) {
				if ($voorkeur->isPosted() AND $voorkeur->save()) {
					setMelding('Voorkeuren opgeslagen', 1);
				}
				$midden = new ProfielVoorkeurContent($voorkeur, $actie);
			} else {
				$midden = new ProfielContent(LidCache::getLid($uid));
			}
			break;

		case 'wachtwoord':
			if (LoginModel::mag('P_ADMIN')) {
				$lid = LidCache::getLid($uid);
				if (isset($_POST[$actie]) AND ! empty($_POST[$actie])) {
					$wachtwoord = filter_input(INPUT_POST, $actie, FILTER_SANITIZE_STRING);
				} else {
					$wachtwoord = null;
				}
				if ($lid instanceof Lid AND $lid->resetWachtwoord($wachtwoord)) {
					setMelding('Wachtwoord succesvol gereset.', 1);
				} else {
					setMelding('Wachtwoord resetten mislukt.', -1);
				}
			}
			redirect(CSR_ROOT . '/profiel/' . $uid);
			break;

		case 'addToGoogleContacts';
			require_once 'googlesync.class.php';
			GoogleSync::doRequestToken(CSR_ROOT . '/profiel/' . $uid . '/addToGoogleContacts');

			$gSync = GoogleSync::instance();
			$message = $gSync->syncLid($uid);
			setMelding('<h3>Opgeslagen in Google Contacts:</h3>' . $message, 2);
			redirect(CSR_ROOT . '/profiel/' . $uid);
			break;

		/** @noinspection PhpMissingBreakStatementInspection */
		case 'rssToken':
			if ($uid == LoginModel::getUid()) {
				LoginModel::instance()->getLid()->generateRssToken();
				redirect(CSR_ROOT . '/profiel/' . $uid . '#forum');
			}
		//geen break hier, want als de bovenstaande actie aangevraagd werd voor de
		//niet-huidige gebruiker, doen we gewoon een normale view.
		case 'view':
		default;
			$lid = LidCache::getLid($uid);
			if (!$lid instanceof Lid) {
				setMelding('<h3>Helaas</h3>Dit lid bestaat niet.<br /> U kunt verder zoeken in deze ledenlijst.', -1);
				redirect(CSR_ROOT . '/ledenlijst/');
			}
			$midden = new ProfielContent($lid);
	}
}

$pagina = new CsrLayoutPage($midden);
$pagina->addCompressedResources('profiel');
$pagina->addCompressedResources('grafiek');
$pagina->view();
