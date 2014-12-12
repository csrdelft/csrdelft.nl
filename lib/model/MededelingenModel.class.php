<?php

require_once 'model/MededelingCategorieModel.class.php';

/**
 * MededelingenModel.class.php	|  Maarten Somhorst
 *
 *
 */
class MededelingenModel {

	private $id = 0;
	private $datum;
	private $vervaltijd;
	private $uid;
	private $titel;
	private $tekst;
	private $zichtbaarheid;
	private $doelgroep;
	private $categorieId = 0;
	private $prioriteit;
	private $plaatje = '';
	private $categorie = null;

	const defaultPrioriteit = 255;

	public function __construct($init) {
		if (is_array($init)) {
			$this->array2properties($init);
		} else {
			$init = (int) $init;
			if ($init != 0) {
				$this->load($init);
			} else {
				//default waarden voor een nieuwe mededeling
				$this->datum = getDateTime();
				$this->uid = LoginModel::getUid();
				$this->prioriteit = self::defaultPrioriteit;
			}
		}
	}

	public function load($id = 0) {
		$db = MijnSqli::instance();
		$loadQuery = "
			SELECT id, datum, vervaltijd, titel, tekst, categorie, uid, prioriteit, doelgroep, zichtbaarheid, plaatje, categorie
			FROM mededeling
			WHERE id=" . (int) $id . ";";
		$mededeling = $db->getRow($loadQuery);
		if (!is_array($mededeling)) {
			throw new Exception('Mededeling bestaat niet. (Mededeling::load())');
		}
		$this->array2properties($mededeling);
	}

	public function save() {
		$db = MijnSqli::instance();
		if ($this->getPrioriteit() != self::defaultPrioriteit) {
			// Eerst even de prioriteit 'resetten'.
			$prioriteitQuery = "
				UPDATE mededeling
				SET prioriteit=" . self::defaultPrioriteit . "
				WHERE prioriteit=" . (int) $this->getPrioriteit();
			$db->query($prioriteitQuery);
		}
		// Vervaltijd MySQL-NULL maken als hij PHP-null is.
		if ($this->getVervaltijd() === null) {
			$vervaltijd = "NULL";
		} else {
			$vervaltijd = "'" . $this->getVervaltijd() . "'";
		}
		if ($this->getId() == 0) {
			$saveQuery = "
				INSERT INTO mededeling (
					titel, tekst, datum, vervaltijd, uid, prioriteit, doelgroep, zichtbaarheid, categorie, plaatje
				)VALUES(
					'" . $db->escape($this->getTitel()) . "',
					'" . $db->escape($this->getTekst()) . "',
					'" . $this->getDatum() . "',
					" . $vervaltijd . ",
					'" . $this->getUid() . "',
					" . (int) $this->getPrioriteit() . ",
					'" . $this->getDoelgroep() . "',
					'" . $this->getZichtbaarheid() . "',
					" . (int) $this->getCategorieId() . ",
					'" . $db->escape($this->getPlaatje()) . "'
				);";
		} else {
			// Alleen als er een nieuw plaatje is hoeft het plaatjesveld geüpdate te worden.
			// TODO: het oude plaatje verwijderen! 
			$setPlaatje = '';
			if ($this->getPlaatje() != '') {
				$setPlaatje = ",
					plaatje='" . $db->escape($this->getPlaatje()) . "'";
			}
			$saveQuery = "
				UPDATE
					mededeling
				SET
					titel='" . $db->escape($this->getTitel()) . "',
					tekst='" . $db->escape($this->getTekst()) . "',
					datum='" . $this->getDatum() . "',
					vervaltijd=" . $vervaltijd . ",
					uid='" . $this->getUid() . "',
					prioriteit=" . (int) $this->getPrioriteit() . ",
					doelgroep='" . $this->getDoelgroep() . "',
					zichtbaarheid='" . $this->getZichtbaarheid() . "',
					categorie=" . (int) $this->getCategorieId() .
					$setPlaatje . "
				WHERE
					id=" . $this->getId() . "
				LIMIT 1;";
		}
		$queryResult = $db->query($saveQuery);

		$return = -1;
		if ($queryResult) {
			$return = $this->getId();
			if ($return == 0) {
				$return = $db->insert_id();
			}

			// Als er een nieuwe mededeling is toegevoegd die wacht op goedkeuring moeten
			// we de PubCie mailen.
			if ($this->getId() == 0 AND $this->getZichtbaarheid() == 'wacht_goedkeuring') {
				mail('pubcie@csrdelft.nl', 'Nieuwe mededeling wacht op goedkeuring', CSR_ROOT . '/mededelingen/' . $return . "\r\n" .
						"\r\nDe inhoud van de mededeling is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $this->getTekst()) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $this->getUid() . "@csrdelft.nl");
			}
		}
		return $return;
	}

	public function delete() {
		$db = MijnSqli::instance();
		$delete = "UPDATE mededeling SET zichtbaarheid='verwijderd' WHERE id=" . $this->getId() . ";";
		return $db->query($delete);
	}

	public function keurGoed() {
		$this->zichtbaarheid = 'zichtbaar';
		$this->save();
	}

	/*
	 * Vult de attributen van dit object met de waarden in de gegeven array.
	 */

	private function array2properties($array) {
		$this->id = $array['id'];
		$this->titel = $array['titel'];
		$this->tekst = $array['tekst'];
		if ($this->getDatum() === null) { // Als we al een datum hebben (uit de DB), hoeven we het niet te vervangen.
			$this->datum = $array['datum'];
		}
		$this->vervaltijd = $array['vervaltijd'];
		if ($this->getUid() === null) { // Als we al een Uid hebben (uit de DB), hoeven we deze niet te vervangen.
			$this->uid = $array['uid'];
		}
		$this->prioriteit = $array['prioriteit'];
		$this->doelgroep = $array['doelgroep'];
		// Om zichtbaarheid te veranderen moet je moderator zijn en als deze mededeling op goedkeuring wachtte
		// of al verwijderd was, verandert hier niets aan.
		if ($this->getZichtbaarheid() === null OR ( MededelingenModel::isModerator() AND $this->getZichtbaarheid() != 'wacht_goedkeuring' AND $this->getZichtbaarheid() != 'verwijderd')) {
			$this->zichtbaarheid = $array['zichtbaarheid'];
		}
		$this->plaatje = $array['plaatje'];

		$this->categorieId = $array['categorie'];
	}

	public function getId() {
		return $this->id;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getTitelVoorZijbalk() {
		$resultaat = $this->getTitel();
		if (strlen($resultaat) > 21) { //TODO: constante van maken?
			$resultaat = trim(substr($resultaat, 0, 18)) . '…'; //TODO: constanten van maken?
		}
		return $resultaat;
	}

	public function getTekst() {
		return $this->tekst;
	}

	public function getTekstVoorZijbalk() {
		$tijdelijk = preg_replace('/(\[(|\/)\w+\])/', '|', $this->getTekst());
		$resultaat = substr(str_replace(array("\n", "\r", ' '), ' ', $tijdelijk), 0, 40); //TODO: constanten van maken?
		return $resultaat;
	}

	public function getDatum() {
		return $this->datum;
	}

	public function getVervaltijd() {
		return $this->vervaltijd;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getPrioriteit() {
		return $this->prioriteit;
	}

	public function getDoelgroep() {
		return $this->doelgroep;
	}

	public function isPrive() {
		return $this->getDoelgroep() != 'iedereen';
	}

	public function getZichtbaarheid() {
		return $this->zichtbaarheid;
	}

	public function isVerborgen() {
		return $this->getZichtbaarheid() == 'onzichtbaar';
	}

	public function isVerwijderd() {
		return $this->getZichtbaarheid() == 'verwijderd';
	}

	public function getPlaatje() {
		return $this->plaatje;
	}

	public function getCategorieId() {
		return $this->categorieId;
	}

	public function getCategorie($force = false) {
		if ($force OR $this->categorie === null) {
			$this->categorie = new MededelingCategorieModel($this->getCategorieId());
		}
		return $this->categorie;
	}

	public static function getTopmost($aantal, $doelgroep = null) {
		$topmost = array();
		if (!is_numeric($aantal) OR $aantal <= 0) {
			return $topmost;
		}

		$db = MijnSqli::instance();

		// Doelgroep bepalen en checken.
		$doelgroepClause = " AND ";
		switch ($doelgroep) {
			case 'nietleden':
				$doelgroepClause.="doelgroep='iedereen'";
				break;
			case 'leden': // De gebruiker mag alleen leden-berichten zien als hij daar rechten toe heeft.
				$doelgroepClause.=self::magPriveLezen() ? "doelgroep!='oudleden'" : "doelgroep='iedereen'"; // Let op de != en =
				break;
			case 'oudleden': // De gebruiker mag alleen oudlid-berichten zien als hij oudlid of moderator is.
				if (self::isOudlid() OR self::isModerator()) {
					$doelgroepClause.="doelgroep!='leden'";
				} elseif (self::magPriveLezen()) { // Anders mag een normaal lid ledenberichten zien én de berichten voor iedereen.
					$doelgroepClause.="doelgroep!='oudleden'";
				} else { // Anders mag een niet-lid alleen de berichten zien die voor iedereen bestemd zijn.
					$doelgroepClause.="doelgroep='iedereen'";
				}
				break;
			default:
				// Indien $doelgroep niet is opgegeven of ongeldig is, kijken we wat het beste past bij de huidige gebruiker.
				if (self::isOudlid()) {
					$doelgroepClause.="doelgroep!='leden'";
				} elseif (self::magPriveLezen()) {
					$doelgroepClause.="doelgroep!='oudleden'";
				} else {
					$doelgroepClause.="doelgroep='iedereen'";
				}
				break;
		}

		$topmostQuery = "
			SELECT 
				id, datum, vervaltijd, titel, tekst, categorie, uid, 
				prioriteit, doelgroep, zichtbaarheid, plaatje, categorie
			FROM mededeling
			WHERE (vervaltijd IS NULL OR vervaltijd > '" . getDateTime() . "')
			  AND zichtbaarheid='zichtbaar'" . $doelgroepClause . "
			ORDER BY prioriteit ASC, datum DESC
			LIMIT " . $aantal;
		$resource = $db->select($topmostQuery);
		while ($mededeling = $db->next($resource)) {
			$topmost[] = new MededelingenModel($mededeling);
		}
		return $topmost;
	}

	public static function getLijstVanPagina($pagina = 1, $aantal, $prullenbak = false) {
		// Prullenbak checken.
		if ($prullenbak AND ! MededelingenModel::isModerator()) {
			$prullenbak = false;
		}

		// Initialisaties.
		$mededelingen = array();
		$db = MijnSqli::instance();
		list($vervalClause, $operator, $verborgenClause, $doelgroepClause) = MededelingenModel::getClauses($prullenbak);

		$paginaQuery = "
			SELECT id, datum
			FROM mededeling
			WHERE (" . $vervalClause . "
			" . $operator . " " . $verborgenClause . ")" . $doelgroepClause . "
			ORDER BY datum DESC
			LIMIT " . (($pagina - 1) * $aantal) . ", " . $aantal;
		$resource = $db->select($paginaQuery);
		while ($mededeling = $db->next($resource)) {
			$groepeerstring = strftime('%B %Y', strtotime($mededeling['datum'])); // Maand voluit en jaar.
			if (!isset($mededelingen[$groepeerstring]))
				$mededelingen[$groepeerstring] = array();
			$mededelingen[$groepeerstring][] = new MededelingenModel($mededeling['id']);
		}
		return $mededelingen;
	}

	public static function getLijstWachtGoedkeuring() {
		$mededelingen = array();
		// Moderators of niet-ingelogden hebben geen berichten die wachten op goedkeuring.
		if (MededelingenModel::isModerator() OR ! LoginModel::mag('P_LEDEN_READ'))
			return $mededelingen;

		$db = MijnSqli::instance();
		$query = "
			SELECT id, datum
			FROM mededeling
			WHERE uid='" . LoginModel::getUid() . "' 
			AND zichtbaarheid='wacht_goedkeuring'
			ORDER BY datum DESC";
		$resource = $db->select($query);
		while ($mededeling = $db->next($resource)) {
			$datum = date_create($mededeling['datum']);
			$groepeerstring = $datum->format('F Y'); // Maand voluit en jaar.
			if (!isset($mededelingen[$groepeerstring]))
				$mededelingen[$groepeerstring] = array();
			$mededelingen[$groepeerstring][] = new MededelingenModel($mededeling['id']);
		}
		return $mededelingen;
	}

	public static function getAantal($prullenbak) {
		$db = MijnSqli::instance();
		list($vervalClause, $operator, $verborgenClause, $doelgroepClause) = MededelingenModel::getClauses($prullenbak);

		$aantalQuery = "
			SELECT COUNT(*) as aantal
			FROM mededeling
			WHERE (" . $vervalClause . "
			" . $operator . " " . $verborgenClause . ")" . $doelgroepClause;
		$resource = $db->select($aantalQuery);
		$resultaat = $db->next($resource);
		return (int) $resultaat['aantal'];
	}

	public function getPaginaNummer($prullenbak) {
		$db = MijnSqli::instance();
		list($vervalClause, $operator, $verborgenClause, $doelgroepClause) = MededelingenModel::getClauses($prullenbak);

		$positieQuery = "
			SELECT COUNT(*) as positie
			FROM mededeling
			WHERE (" . $vervalClause . "
			" . $operator . " " . $verborgenClause . ")" . $doelgroepClause . "	AND datum>='" . $this->getDatum() . "'";

		$resource = $db->select($positieQuery);
		$record = $db->next($resource);
		$paginaNummer = (int) ceil(($record['positie']) / LidInstellingen::get('mededelingen', 'aantalPerPagina'));
		$paginaNummer = $paginaNummer >= 1 ? $paginaNummer : 1; // Het moet natuurlijk wel groter dan 0 zijn.
		return $paginaNummer;
	}

	public static function getLaatsteMededelingen($aantal) {
		$resultaat = array();
		$db = MijnSqli::instance();
		$zichtbaarheidClause = "zichtbaarheid='zichtbaar'";
		$doelgroepClause = "";
		if (!LoginModel::mag('P_LEDEN_READ')) {
			$doelgroepClause = " AND doelgroep='iedereen'";
		}
		$laatstenQuery = "
			SELECT id, datum, vervaltijd, titel, tekst, categorie, uid, prioriteit, doelgroep, zichtbaarheid, plaatje, categorie
			FROM mededeling
			WHERE (vervaltijd IS NULL OR vervaltijd > '" . getDateTime() . "') 
			AND " . $zichtbaarheidClause . $doelgroepClause . "
			ORDER BY datum DESC, id DESC
			LIMIT " . (int) $aantal;
		$resource = $db->select($laatstenQuery);

		while ($mededelingRecord = $db->next($resource)) {
			$resultaat[] = new MededelingenModel($mededelingRecord);
		}
		return $resultaat;
	}

	public function resetPrioriteit() {
		$updatePrioriteit = "
			UPDATE mededeling
			SET	prioriteit='" . MededelingenModel::defaultPrioriteit . "'
			WHERE prioriteit='" . $this->getPrioriteit() . "';";
		return MijnSqli::instance()->query($updatePrioriteit);
	}

	public static function getPrioriteiten() {
		$prioriteiten = array();
		$prioriteiten[255] = 'geen';
		for ($i = 1; $i <= 6; $i++) {
			$prioriteiten[$i] = 'Prioriteit ' . $i;
		}
		return $prioriteiten;
	}

	public static function getDoelgroepen() {
		return array('iedereen', '(oud)leden', 'leden');
	}

	// function magBewerken()
	// post: geeft true terug als het huidige lid deze Mededeling mag bewerken of verwijderen. Anders, false.
	public function magBewerken() {
		// het huidige lid mag dit bericht alleen bewerken als hij moderator is of als dit zijn eigen bericht
		// is (en hij dus het toevoeg-recht heeft).
		return MededelingenModel::isModerator() OR ( MededelingenModel::magToevoegen() AND $this->getUid() == LoginModel::getUid());
	}

	public static function isModerator() {
		return LoginModel::mag('P_NEWS_MOD');
	}

	public static function isOudlid() {
		return LoginModel::mag('P_ALLEEN_OUDLID');
	}

	// function magPriveLezen()
	// post: geeft true terug als het huidige lid prive-Mededelingen mag lezen (berichten die voor leden bestemd zijn).
	public static function magPriveLezen() {
		return LoginModel::mag('P_LEDEN_READ');
	}

	// function magToevoegen()
	// post: geeft true terug als het huidige lid Mededelingen mag toevoegen.
	public static function magToevoegen() {
		return LoginModel::mag('P_NEWS_POST');
	}

	public static function knipTekst($sTekst, $iMaxTekensPerRegel = 26, $iMaxRegels = 2) {
		$iTekensOver = $iMaxTekensPerRegel; // Aantal tekens die over zijn voor de huidige (resultaat)regel.
		$iRegelsOver = $iMaxRegels - 1; // Aantal (resultaat)regels die nu over/leeg zijn.
		$sRegelAfsluiting = '<br />';

		$sResultaat = '';
		$aRegelsInTekst = explode($sRegelAfsluiting, $sTekst);
		// Per (bron)regel (volgens de newlines in $sTekst)
		for ($i = 0; $i < $iMaxRegels AND $i < count($aRegelsInTekst); $i++) {
			$sRegel = $aRegelsInTekst[$i];
			$iRegelLengte = strlen(strip_tags($aRegelsInTekst[$i])); // Wel even de tags eruit slopen, want we moeten niet vals spelen.
			if ($iRegelLengte <= $iTekensOver) { // Er is genoeg plek op de huidige (resultaat)regel.
				// Bronregel toevoegen aan de resultaatregel.
				$sResultaat.=$sRegel;
				// Nieuwe (resultaat)regel markeren.
				$iRegelsOver--;
				$iTekensOver = $iMaxTekensPerRegel;
			} else { // Er is niet genoeg plek op de huidige regel.
				// Alle woorden printen die nog passen.
				$aWoordenInRegel = explode(' ', $sRegel);
				// Per woord in deze regel.
				foreach ($aWoordenInRegel as $sWoord) {
					$aTagsInWoord = explode('<', $sWoord);
					// Per tag in dit woord.
					for ($k = 0; $k < count($aTagsInWoord); $k++) {
						$sTag = $aTagsInWoord[$k];
						$iPositieEindTag = strpos($sTag, '>');
						// De woordlengte bepalen.
						if ($iPositieEindTag === false) { // De tag is nog niet beëindigd.
							if ($k != 0) { // De eerste moeten we nooit als tag zien.
								$iWoordLengte = 0;
							} else { // Maar bij de eerste moeten er wel tekens vanaf getrokken worden.
								$iWoordLengte = strlen($sTag);
							}
						} else { // De tag wordt wél beëindigd.
							$iWoordLengte = strlen($sTag) - ($iPositieEindTag + 1); // De lengte v/d string ná de tag.
						}
						if (($ampPos = strpos($sTag, '&') ) !== false AND ( $semiPos = strpos($sTag, ';')) !== false AND ( $diff = $semiPos - $ampPos ) >= 3 AND
								$diff <= 7
						) {
							//Dus, als er een enkele entiteit in $sTag zit, corrigeren we de woordlengte. We definiëren
							//een entiteit als een string die begint met een '&', eindigt met een ';' met daartussen 2 tot 6
							//karakters.
							$iWoordLengte-=$diff;
						}

						// En nu gaan we kijken of het woord past.
						if ($iWoordLengte + 1 <= $iTekensOver) {
							// Het woord past, dus toevoegen.
							if ($k != 0) {
								$sResultaat .= '<';
							}
							$sResultaat.=$sTag;
							$iTekensOver-=$iWoordLengte + 1;
						} elseif ($iWoordLengte <= $iMaxTekensPerRegel AND $iRegelsOver > 0) { // Het woord past op de volgende regel.
							// Woord toevoegen.
							if ($k != 0) {
								$sResultaat .= '<';
							}
							$sResultaat.=$sTag;
							// Nieuwe regel markeren.
							$iRegelsOver--;
							$iTekensOver = $iMaxTekensPerRegel - $iWoordLengte;
							$i++;
						} else { // Het woord past niet op deze regel én niet op een (eventuele) volgende regel.
							if (substr($sTag, 0, 1) == '/' AND $iPositieEindTag !== false) { // Er wordt een tag beëindigd! Even printen dus.
								$sResultaat .= '<' . substr($sTag, 0, $iPositieEindTag);
							}
							$sResultaat .= '…';
							$bStopDeBoel = true;
							break;
						}
					} // Einde iedere tag in dit woord.
					if (isset($bStopDeBoel) AND $bStopDeBoel)
						break;
					$sResultaat .= ' ';
				} // Einde ieder woord in deze regel.
			}
			$sResultaat.=$sRegelAfsluiting;
		} // Einde iedere (bron)regel.
		// Indien het resultaat eindigt op een regelafsluiting-tag, halen we die even weg.
		$iLengteRegelAfsluiting = strlen($sRegelAfsluiting);
		if (substr($sResultaat, strlen($sResultaat) - $iLengteRegelAfsluiting, $iLengteRegelAfsluiting) == $sRegelAfsluiting) {
			$sResultaat = substr($sResultaat, 0, strlen($sResultaat) - $iLengteRegelAfsluiting);
		}
		if ($iRegelsOver <= 0 AND isset($aRegelsInTekst[$i + 1])) { // Indien er geen regels meer over zijn, maar wel tekst.
			$sEind = substr($sResultaat, strlen($sResultaat) - 1, 1); // Laatste teken ophalen.
			// Alleen de puntjes erachter zetten als dit nog niet gedaan is doordat er een woord niet meer paste.
			if ($sEind != '…') {
				$sResultaat .= '…';
			}
		}
		return $sResultaat;
	}

	// static function getClauses(bool)
	// Geeft een array met clause terug, rekening houdend met of we op de prullenbak-pagina zijn of niet.
	public static function getClauses($prullenbak) {
		// Verval clause.
		$vervalClause = "(vervaltijd IS NULL OR vervaltijd > '" . getDateTime() . "')";
		if ($prullenbak) {
			$vervalClause = "(vervaltijd IS NOT NULL AND vervaltijd <= '" . getDateTime() . "')";
		}
		// Operator tussen de verval clause en verborgen clause.
		$operator = "AND";
		if ($prullenbak) {
			$operator = "OR";
		}
		// Verborgen clause.
		$verborgenClause = "zichtbaarheid='zichtbaar'";
		if ($prullenbak) {
			$verborgenClause = "(zichtbaarheid='verwijderd' OR zichtbaarheid='onzichtbaar')";
		} elseif (MededelingenModel::isModerator()) { // Als de gebruiker moderator is, mag hij ook wacht_goedkeuring-berichten zien.
			$verborgenClause = "(zichtbaarheid='zichtbaar' OR zichtbaarheid='wacht_goedkeuring')";
		}
		// Doelgroep clause.
		$doelgroepClause = "";
		if (!LoginModel::mag('P_LEDEN_READ')) {
			$doelgroepClause = " AND doelgroep='iedereen'";
		} elseif (self::isOudlid()) {
			$doelgroepClause = " AND doelgroep!='leden'";
		}

		return array($vervalClause, $operator, $verborgenClause, $doelgroepClause);
	}

}
