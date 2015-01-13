<?php

require_once 'model/entity/groepen/OldGroep.class.php';

/**
 * OldGroepModel.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Groepen zijn als volgt in de db opgeslagen:
 * groeptype:	Verschillende 'soorten' groepen: commissies, woonoorden, etc.
 * groep:		De daadwerkelijke groepen.
 * groeplid:	De leden van verschilllende groepen.
 *
 * leden kunnen uiteraard lid zijn van verschillende groepen, maar niet meer
 * dan één keer in een bepaalde groep zitten.
 *
 * Deze klasse is een verzameling van groepobjecten van een bepaald type. Standaard
 * worden alleen de h.t.-groepen opgehaald.
 */
class GroepenOldModel {

	private $type;
	private $groepen = null;

	/*
	 * Constructor voor Groepen.
	 *
	 * @param	$groeptype		Welke groepen moeten geladen worden?
	 * @return 	void
	 */

	public function __construct($groeptype) {
		$db = MijnSqli::instance();

		if (is_int($groeptype)) {
			$where = "groeptype.id=" . (int) $groeptype;
		} else {
			$where = "groeptype.naam='" . $db->escape($groeptype) . "'";
		}

		//we laden eerst de gegevens over de groep op
		$query = "
			SELECT id, naam, beschrijving, toonHistorie, groepenAanmaakbaar, syncWithLDAP FROM groeptype
			WHERE " . $where . " LIMIT 1;";
		$categorie = $db->getRow($query);
		if (is_array($categorie)) {
			$this->type = $categorie;
		} else {
			$message = 'Groeptype (' . $groeptype . ') bestaat niet! Groepen::__construct()';
			if (LoginModel::mag('P_ADMIN')) {
				$message.="\n" . $db->error();
			}
			throw new Exception($message);
		}
	}

	/*
	 * De gevens van het groeptype ophalen, met de bekende groepen voor
	 * het type.
	 */

	private function loadGroepen() {
		$db = MijnSqli::instance();

		//Afhankelijk van de instelling voor het groeptype halen we alleen de
		//h.t.-groepen op, of ook de o.t.-groepen.
		$htotFilter = "groep.status='ht'";
		$sort = '';
		if ($this->getToonHistorie()) {
			$htotFilter.=" OR groep.status='ot'";
			$sort = "groep.begin DESC, groep.id ASC, ";
		}

		$qGroepen = "
			SELECT
				groep.id AS groepId, groep.gtype AS gtypeId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groep.status AS status, begin, einde, aanmeldbaar, functiefilter, limiet, toonFuncties, toonPasfotos, lidIsMod, eigenaar,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit,
				IF(aanmeldbaar = '',
					1,
					IF(einde>=CURRENT_DATE() OR einde = 0000-00-00,
						IF(limiet = 0 OR 
							((
								SELECT count( * )
								FROM groeplid gl
								WHERE gl.groepid = groep.id
							)-limiet) < 0,
							0,
							1
						),
						1 
					)
				)AS vol
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			LEFT JOIN lid ON(groeplid.uid=lid.uid)
			WHERE groep.gtype=" . $this->getId() . "
			  AND groep.zichtbaar='zichtbaar'
			  AND (" . $htotFilter . ")
			ORDER BY " . $sort . " vol ASC, groep.snaam ASC, groeplid.prioriteit ASC, lid.achternaam ASC, lid.voornaam;";
		$rGroepen = $db->query($qGroepen);
		//nu een beetje magic om een stapeltje groepobjecten te genereren:
		$currentGroepId = null;
		$aGroep = array();
		while ($aGroepraw = $db->next($rGroepen)) {
			//eerste groepid in de huidige groep stoppen
			if ($currentGroepId == null) {
				$currentGroepId = $aGroepraw['groepId'];
			}

			//zijn we bij een volgende groep aangekomen?
			if ($currentGroepId != $aGroepraw['groepId']) {
				//groepobject maken en aan de array toevoegen

				$this->groepen[$aGroep[0]['groepId']] = new OldGroep($aGroep);

				//tenslotte nieuwe groep als huidige kiezen en groeparray leegmikken
				$currentGroepId = $aGroepraw['groepId'];
				$aGroep = array();
			}
			$aGroep[] = $aGroepraw;
		}

		if (isset($aGroep[0])) {
			//tot slot de laatste groep ook toevoegen
			$this->groepen[$aGroep[0]['groepId']] = new OldGroep($aGroep);
		}
	}

	/*
	 * Sla de huidige toestand van het groeptype op in de database.
	 * LET OP: deze methode doet niets met de ingeladen groepen.
	 */

	public function save() {
		$db = MijnSqli::instance();
		$qSave = "
			UPDATE groeptype
			SET beschrijving='" . $db->escape($this->getBeschrijving()) . "'
			WHERE id=" . $this->getId() . "
			LIMIT 1;";
		return $db->query($qSave);
	}

	public function getGroepen() {
		if ($this->groepen === null) {
			$this->loadGroepen();
		}
		return $this->groepen;
	}

	public function getId() {
		return $this->type['id'];
	}

	public function getNaam() {
		return $this->type['naam'];
	}

	public function getNaamEnkelvoud($lcfirst = false) {
		//genereer enkelvoud
		if ($this->type['naam'] == 'Besturen') {
			$naam = 'Bestuur';
		} elseif (substr($this->type['naam'], -2) == 'en') {
			$naam = substr($this->type['naam'], 0, -2);
		} elseif (substr($this->type['naam'], -1) == 's' AND $this->type['naam'] != 'Dies') {
			$naam = substr($this->type['naam'], 0, -1);
		} else {
			$naam = $this->type['naam'] . '-ketzer';
		}
		//Eerste letter geen hoofdletter
		if ($lcfirst) {
			$naam = lcfirst($naam);
		}
		return $naam;
	}

	public function getBeschrijving() {
		return $this->type['beschrijving'];
	}

	public function setBeschrijving($beschrijving) {
		$this->type['beschrijving'] = trim($beschrijving);
	}

	public function getToonHistorie() {
		return $this->type['toonHistorie'] == 1;
	}

	public function getSyncWithLDAP() {
		return $this->type['syncWithLDAP'] == 1;
	}

	public function getGroepAanmaakbaarPermissies() {
		return $this->type['groepenAanmaakbaar'];
	}

	public function isGroepAanmaker() {
		return LoginModel::mag($this->getGroepAanmaakbaarPermissies());
	}

	public static function isAdmin() {
		return LoginModel::mag('P_LEDEN_MOD');
	}

	public function getGroep($groepId) {
		if ($this->groepen === null) {
			$this->loadGroepen();
		}
		if (isset($this->groepen[$groepId])) {
			return $this->groepen[$groepId];
		}
		return false;
	}

	//Alle h.t. groepen in een categorie o.t. maken.
	public function maakGroepenOt() {
		$error = '';
		if ($this->groepen === null) {
			$this->loadGroepen();
		}
		if (count($this->groepen) == 0) {
			return true;
		}
		foreach ($this->groepen as $groep) {
			if (!$groep->maakOt()) {
				$error .= '';
			}
		}
		return $error == '';
	}

	/*
	 * statische functie om de groepen bij een gebruiker te zoeken.
	 *
	 * @param	$uid	Gebruiker waarvoor groepen moeten worden opgezocht
	 * @return			Array met Groep-objectjes
	 */

	public static function getByUid($uid) {
		$db = MijnSqli::instance();

		$groepen = array();
		if (AccountModel::isValidUid($uid)) {
			$qGroepen = "
				SELECT
					groep.id AS id
				FROM groep
				INNER JOIN groeptype ON(groep.gtype=groeptype.id)
				WHERE groeptype.toonProfiel=1
				  AND groep.id IN (
					SELECT groepid FROM groeplid WHERE uid = '" . $uid . "'
				)
				ORDER BY groep.status, groeptype.prioriteit, groep.naam;";

			$rGroepen = $db->query($qGroepen);
			if ($rGroepen !== false and $db->numRows($rGroepen) > 0) {
				while ($row = $db->next($rGroepen)) {
					$groepen[] = new OldGroep($row['id']);
				}
			}
		}
		return $groepen;
	}

	/*
	 * statische functie om de groepen bij een gebruiker te zoeken 
	 * waarvan ie in de wiki pagina's mag wijzigen
	 *
	 * @param	$uid	Gebruiker waarvoor groepen moeten worden opgezocht
	 * @return			Array met de kortenamen van de groepen
	 */

	public static function getWikigroupsByUid($uid) {
		$db = MijnSqli::instance();

		$groepen = array();
		if (AccountModel::isValidUid($uid)) {
			$qGroepen = "
				SELECT
					DISTINCT g.snaam as kortenaam
				FROM 
					groep g
				INNER JOIN 
					groeptype ON(g.gtype=groeptype.id)
				WHERE 
					groeptype.syncWithLDAP=1
					AND g.id IN (
						SELECT groepid FROM groeplid WHERE uid = '" . $uid . "'
					)
					AND (
						g.status IN ('ft', 'ht')
						OR g.id = (
							SELECT id
							FROM groep
							WHERE status='ot' AND snaam=g.snaam
							ORDER BY begin DESC
							LIMIT 1
						)
					);";
			$rGroepen = $db->query($qGroepen);
			if ($rGroepen !== false and $db->numRows($rGroepen) > 0) {
				while ($row = $db->next($rGroepen)) {
					$groepen[] = $row['kortenaam'];
				}
			}
			//leden en oudleden krijgen een extra groep 'htleden'
			$profiel = ProfielModel::get($uid);
			//S_CIEs die wel als normaal lid mogen inloggen
			$magLidtoegang = array('x271', 'x030'); //oudledenbestuur & stichting CC
			if ($profiel->isLid() OR $profiel->isOudlid() OR in_array($profiel->uid, $magLidtoegang)) {
				$groepen[] = 'htleden-oudleden';
			}
		}
		return $groepen;
	}

	/*
	 * Haal de huidige groepen van een bebaald type voor een bepaald lid.
	 */

	public static function getByTypeAndUid($type, $uid, $status = 'ht') {
		$db = MijnSqli::instance();

		$groepen = array();
		if (AccountModel::isValidUid($uid)) {
			if ($status != null AND in_array($status, array('ht', 'ft', 'ot'))) {
				$statusfilter = " AND status='" . $status . "' ";
			} else {
				$statusfilter = '';
			}
			$qGroepen = "
				SELECT id
				FROM groep
				WHERE gtype IN (
					SELECT id
					FROM groeptype
					WHERE id=" . (int) $type . "
					" . $statusfilter . "
				) AND id IN (
					SELECT groepid FROM groeplid WHERE uid = '" . $uid . "'
				);";
			$rGroepen = $db->query($qGroepen);
			if ($rGroepen !== false and $db->numRows($rGroepen) > 0) {
				while ($row = $db->next($rGroepen)) {
					$groepen[] = new OldGroep($row['id']);
				}
			}
		}
		return $groepen;
	}

	/*
	 * Is lid in gevraagde groep
	 *
	 */

	/**
	 * Is lid van gevraagde groep
	 * @static
	 * @param $uid
	 * @param $groep kortenaam van de groep
	 * @param array|komma|string $status komma gescheiden lijst van gewenste groepstati
	 * @return bool wel/niet lid
	 */
	public static function isUidLidofGroup($uid, $groep, $status = array('ft', 'ht', 'ot')) {
		$db = MijnSqli::instance();
		$qLookup = "
			SELECT uid
			FROM groeplid gl
			LEFT JOIN groep g ON(g.id=gl.groepid)
			WHERE g.snaam='" . $db->escape($groep) . "'
						AND gl.uid='" . $db->escape($uid) . "'
							AND g.status IN('" . implode("','", $status) . "');";
		$rLookup = $db->query($qLookup);
		return $rLookup !== false AND $db->numRows($rLookup) > 0;
	}

	/*
	 * Statische functie om een verzameling van groeptypes terug te geven
	 *
	 * @return		Array met groeptypes
	 */

	public static function getGroeptypes($alleenZichtbaar = true) {
		$db = MijnSqli::instance();
		$qGroeptypen = "
			SELECT id, naam
			FROM groeptype ";
		if ($alleenZichtbaar === true) {
			$qGroeptypen.="WHERE zichtbaar=1 ";
		}
		$qGroeptypen.="ORDER BY prioriteit ASC, naam ASC;";
		$rGroeptypen = $db->query($qGroeptypen);
		return $db->result2array($rGroeptypen);
	}

	public static function isValidGtype($gtypetotest) {
		$db = MijnSqli::instance();
		$qGroep = "SELECT id FROM groeptype WHERE naam='" . $db->escape($gtypetotest) . "'";
		return $db->numRows($db->query($qGroep)) == 1;
	}

	/*
	 * Statische functie die de werkgroepleiders teruggeeft
	 *
	 * @return		Array met uid van werkgroepleiders
	 */

	public static function getWerkgroepLeiders() {
		$db = MijnSqli::instance();
		$Werkgroepleiders = "
			SELECT uid
			FROM groeplid
			WHERE (functie='Leider' OR functie='leider')
			AND groepid IN (
				SELECT groep.id
				FROM groep JOIN groeptype ON groep.gtype = groeptype.id
				WHERE groeptype.naam='Werkgroepen' AND groep.status='ht')";
		$result = $db->result2array($db->query($Werkgroepleiders));
		$leiders = array();
		if (is_array($result)) {
			foreach ($result as $leider) {
				array_push($leiders, $leider['uid']);
			}
		}
		return $leiders;
	}

	/**
	 * Zoek groepen die matchen op id, snaam of naam
	 *
	 * @static
	 * @param string $zoekterm
	 * @param int $gtype
	 * @param int $limiet
	 * @return array
	 */
	public static function zoekGroepen($zoekterm, $gtype = 0, $limiet = 0) {
		$db = MijnSqli::instance();
		$groepen = array();
		$wheretype = "";
		if ($gtype != 0) {
			$wheretype = " AND gtype = " . (int) $gtype;
		}
		$query = "
			SELECT g.id AS id, gt.naam AS type, snaam, g.naam AS naam, status
			FROM groep g
			LEFT JOIN groeptype gt ON (gt.id = g.gtype)
			WHERE g.zichtbaar = 'zichtbaar' " . $wheretype . " AND
				(g.snaam LIKE '%" . $db->escape($zoekterm) . "%' OR g.naam LIKE '%" . $db->escape($zoekterm) . "%')
			ORDER BY g.begin DESC
		";
		if ((int) $limiet > 0) {
			$query .= "LIMIT 0, " . (int) $limiet;
		}
		$query .= ";";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			while ($prop = $db->next($result)) {
				//$status = str_split($prop['status']);
				//$status = '<span class="lichtgrijs">' . $status[0] . '.' . $status[1] . '. </span>';
				$groepen[] = array(
					'url'	 => '/groepen/' . $prop['type'] . '/' . $prop['id'],
					'value'	 => $prop['naam'] . '<span class="lichtgrijs"> - ' . $prop['type'] . '</span>'
				);
			}
		}
		return $groepen;
	}

}
