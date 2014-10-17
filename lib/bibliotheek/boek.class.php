<?php

require_once 'rubriek.class.php';
require_once 'beschrijving.class.php';

/**
 * boek.class.php	| 	Gerrit Uitslag
 *
 * boeken
 *
 */
class Boek {

	protected $id = 0;   //boekId
	protected $titel;   //String
	protected $auteur;   //String Auteur
	protected $rubriek = null; //Rubriek object
	protected $uitgavejaar;
	protected $uitgeverij;
	protected $paginas;
	protected $taal = 'Nederlands';
	protected $isbn;
	protected $code;
	protected $status; //'beschikbaar'/'teruggeven'/'geen'
	protected $biebboek = 'nee'; //'ja'/'nee'
	protected $error = '';
	protected $exemplaren = null; // array
	protected $formulier; // Form objecten voor recensieformulier of nieuwboekformulier

	public function __construct($init) {
		$this->load($init);
	}

	/**
	 * Laad object Boek afhankelijk van parameters van de constructor
	 *
	 * @param array|int $init met eigenschappen of integer boekId (niet 0)
	 * @throws Exception
	 */
	private function load($init = 0) {
		if (is_array($init)) {
			$this->array2properties($init);
		} else {
			$this->id = (int) $init;
			if ($this->getId() != 0) {
				$db = MijnSqli::instance();
				$query = "
					SELECT id, titel, auteur, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code,
					IF((
						SELECT count( * )
						FROM biebexemplaar e2
						WHERE e2.boek_id = biebboek.id AND e2.status='beschikbaar'
						) > 0, 
					'beschikbaar', 
						IF((
							SELECT count( * )
							FROM biebexemplaar e2
							WHERE e2.boek_id = biebboek.id AND e2.status='teruggegeven'
							) > 0,
						'teruggegeven',
						'geen'
						)
					) AS status
					FROM biebboek
					WHERE Id=" . $this->getId() . ";";
				$boek = $db->getRow($query);
				if (is_array($boek)) {
					$this->array2properties($boek);
				} else {
					throw new Exception('load() mislukt. Bestaat het boek wel? ' . $db->error());
				}
			} else {
				throw new Exception('load() mislukt. Boekid = 0');
			}
		}
	}

	/**
	 * Eigenschappen in object stoppen
	 *
	 * @param array $properties met eigenschappen, setValue() moet de keys kennen
	 */
	private function array2properties($properties) {
		foreach ($properties as $prop => $value) {
			$this->setValue($prop, $value, $initboek = true);
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getUitgavejaar() {
		return $this->uitgavejaar;
	}

	public function getUitgeverij() {
		return $this->uitgeverij;
	}

	public function getPaginas() {
		return $this->paginas;
	}

	public function getTaal() {
		return $this->taal;
	}

	public function getISBN() {
		return $this->isbn;
	}

	public function getCode() {
		return $this->code;
	}

	public function getAuteur() {
		return $this->auteur;
	}

	/**
	 * @return Rubriek
	 */
	public function getRubriek() {
		return $this->rubriek;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getError() {
		return $this->error;
	}

	//url naar dit boek
	public function getUrl() {
		return CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->getId();
	}

	/**
	 * Returns an array of eigenaaruids van boek of exemplaar
	 *
	 * @param null|int $exemplaarid
	 * @return array
	 */
	public function getEigenaars($exemplaarid = null) {
		$db = MijnSqli::instance();
		if ($exemplaarid == null) {
			$where = "WHERE boek_id =" . (int) $this->getId();
		} else {
			$where = "WHERE id =" . (int) $exemplaarid;
		}
		$qEigenaar = "
			SELECT eigenaar_uid
			FROM  `biebexemplaar` 
			" . $where . ";";
		$result = $db->query($qEigenaar);

		$eigenaars = array();
		if ($db->numRows($result) > 0) {
			while ($eigenaar = $db->next($result)) {
				$eigenaars[] = $eigenaar['eigenaar_uid'];
			}
		}
		return $eigenaars;
	}

	/**
	 * Controleert rechten voor wijderactie
	 *
	 * @return	bool
	 * 		boek mag alleen door admins verwijdert worden
	 */
	public function magVerwijderen() {
		return LoginModel::mag('groep:BAS-FCie,P_BIEB_MOD,P_ADMIN');
	}

	/**
	 * Controleert rechten voor bewerkactie
	 *
	 * @return	bool
	 * 		boek mag alleen door admins of door eigenaar v.e. exemplaar bewerkt worden
	 */
	public function magBewerken() {
		return LoginModel::mag('P_BIEB_EDIT') OR $this->isEigenaar() OR $this->magVerwijderen();
	}

	/**
	 * Iedereen met extra rechten en zij met BIEB_READ mogen
	 */
	public function magBekijken() {
		return LoginModel::mag('P_BIEB_READ') OR $this->magBewerken();
	}

	/**
	 * Controleert of ingelogd eigenaar is van boek/exemplaar
	 *  - Basfcieleden zijn eigenaar van boeken van de bibliotheek
	 *
	 * @param null|int geen of $exemplaarid integer
	 * @return bool true
	 * 				of ingelogd eigenaar is v.e. exemplaar van het boek 
	 * 				of van het specifieke exemplaar als exemplaarid is gegeven.
	 * 			false
	 * 				geen geen resultaat of niet de eigenaar
	 */
	public function isEigenaar($exemplaarid = null) {
		$eigenaars = $this->getEigenaars($exemplaarid);
		foreach ($eigenaars as $eigenaar) {
			if ($eigenaar == LoginModel::getUid()) {
				return true;
			} elseif ($eigenaar == 'x222' AND $this->isBASFCie()) {
				return true;
			}
		}
		return false;
	}

	public function isBASFCie() {
		return LoginModel::mag('groep:BAS-FCie');
	}

	public function isBiebboek($exemplaarid = null) {
		$eigenaars = $this->getEigenaars($exemplaarid);
		foreach ($eigenaars as $eigenaar) {
			if ($eigenaar == 'x222') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check of ingelogd lener is van exemplaar
	 * 
	 * @param int $exemplaarid
	 * @return bool
	 */
	public function isLener($exemplaarid) {
		$db = MijnSqli::instance();
		$qLener = "
			SELECT uitgeleend_uid 
			FROM `biebexemplaar`
			WHERE id=" . (int) $exemplaarid . ";";
		$result = $db->query($qLener);
		if ($db->numRows($result) > 0) {
			$lener = $db->next($result);
			return $lener['uitgeleend_uid'] == LoginModel::getUid();
		} else {
			$this->error.= $db->error();
			return false;
		}
	}

	/**
	 * Verwijder een boek
	 *
	 * @return bool
	 */
	public function delete() {
		if ($this->getId() == 0) {
			$this->error .= 'Kan geen lege boek met id=0 wegkekken. Boek::delete()';
			return false;
		}
		$db = MijnSqli::instance();
		$qDeleteBeschrijvingen = "DELETE FROM biebbeschrijving WHERE boek_id=" . $this->getId() . ";";
		$qDeleteExemplaren = "DELETE FROM biebexemplaar WHERE boek_id=" . $this->getId() . " LIMIT 1;";
		$qDeleteBoek = "DELETE FROM biebboek WHERE id=" . $this->getId() . " LIMIT 1;";
		if ($db->query($qDeleteBeschrijvingen) AND $db->query($qDeleteExemplaren) AND $db->query($qDeleteBoek)) {
			return true;
		} else {
			$this->error .= 'Fout bij verwijderen. Boek::delete() ' . $db->error();
			return false;
		}
	}

	/*	 * ************
	 * Exemplaren *
	 * ************ */

	/**
	 * Laad exemplaren van dit boek in Boek
	 *
	 * @return bool|int
	 */
	public function loadExemplaren() {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, eigenaar_uid, opmerking, uitgeleend_uid, toegevoegd, status, uitleendatum
			FROM biebexemplaar
			WHERE boek_id=" . (int) $this->getId() . "
			ORDER BY toegevoegd;";
		$result = $db->query($query);

		if ($db->numRows($result) > 0) {
			while ($exemplaar = $db->next($result)) {
				$this->exemplaren[$exemplaar['id']] = $exemplaar;
			}
		} else {
			$this->error .= $db->error();
			return false;
		}
		return $db->numRows($result);
	}

	/**
	 * Geeft alle exemplaren van dit boek
	 *
	 * @return array met exemplaren
	 */
	public function getExemplaren() {
		if ($this->exemplaren === null) {
			$this->loadExemplaren();
		}
		return $this->exemplaren;
	}

	/**
	 * Aantal exemplaren
	 *
	 * @return int
	 */
	public function countExemplaren() {
		if ($this->exemplaren === null) {
			$this->loadExemplaren();
		}
		return count($this->exemplaren);
	}

	/**
	 * Geeft status van exemplaar
	 * 
	 * @param int $exemplaarid
	 * @return string statuswaarde uit db van $exemplaarid
	 * 			of anders lege string
	 */
	public function getStatusExemplaar($exemplaarid) {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, status
			FROM biebexemplaar
			WHERE id=" . (int) $exemplaarid . ";";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			$exemplaar = $db->next($result);
			return $exemplaar['status'];
		} else {
			$this->error.= $db->error();
			return '';
		}
	}

	/**
	 * Voeg exemplaar toe
	 *
	 * @param string $eigenaar uid
	 * @return bool true geslaagd
	 * 			false 	mislukt
	 * 					$eigenaar is ongeldig uid
	 */
	public function addExemplaar($eigenaar) {
		if (!Lid::isValidUid($eigenaar)) {
			return false;
		}
		$db = MijnSqli::instance();
		$qSave = "
			INSERT INTO biebexemplaar (
				boek_id, eigenaar_uid, toegevoegd, status
			) VALUES (
				" . (int) $this->getId() . ",
				'" . $db->escape($eigenaar) . "',
				'" . getDateTime() . "',
				'beschikbaar'
			);";
		if ($db->query($qSave)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::addExemplaar()';
		return false;
	}

	/**
	 * Verwijder exemplaar
	 *
	 * @param int $id exemplaarid
	 * @return bool	true geslaagd
	 * 			false mislukt
	 */
	public function verwijderExemplaar($id) {
		$db = MijnSqli::instance();
		$qDeleteExemplaar = "DELETE FROM biebexemplaar WHERE id=" . (int) $id . " LIMIT 1;";
		return $db->query($qDeleteExemplaar);
	}

	/*	 * ****************************************************************************
	 * methodes voor gewone formulieren *
	 * **************************************************************************** */

	/**
	 * Definiëren van de velden van het nieuw boek formulier
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het boekaddding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 *
	 * @param string $naamtitelveld
	 * @return FormElement[]
	 */
	protected function getCommonFields($naamtitelveld = 'Titel') {
		$fields['titel'] = new TitelField('titel', $this->getTitel(), $naamtitelveld, 200);
		$fields['auteur'] = new TextField('auteur', $this->getAuteur(), 'Auteur', 100);
		$fields['auteur']->remotedatasource = '/communicatie/bibliotheek/autocomplete/auteur';
		$fields['auteur']->placeholder = 'Achternaam, Voornaam V.L. van de';
		$fields['paginas'] = new IntField('paginas', $this->getPaginas(), "Pagina's", 0, 10000);
		$fields['taal'] = new TextField('taal', $this->getTaal(), 'Taal', 25);
		$fields['taal']->remotedatasource = '/communicatie/bibliotheek/autocomplete/taal';
		$fields['isbn'] = new TextField('isbn', $this->getISBN(), 'ISBN', 15);
		$fields['isbn']->placeholder = 'Uniek nummer';
		$fields['uitgeverij'] = new TextField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij', 100);
		$fields['uitgeverij']->remotedatasource = '/communicatie/bibliotheek/autocomplete/uitgeverij';
		$fields['uitgavejaar'] = new IntField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar', 0, 2100);
		$fields['rubriek'] = new SelectField('rubriek', $this->getRubriek()->getId(), 'Rubriek', Rubriek::getAllRubrieken($samenvoegen = true, $short = true));
		$fields['code'] = new TextField('code', $this->getCode(), 'Biebcode', 7);
		return $fields;
	}

	/**
	 * Geeft formulier terug
	 *
	 * @return Formulier
	 */
	public function getFormulier() {
		return $this->formulier;
	}

	/**
	 * Controleren of alle velden van formulier correct zijn
	 *
	 * @return bool
	 */
	public function validFormulier() {
		return $this->getFormulier()->validate('');
	}

	/**
	 * Plaats waardes van formulier in object
	 */
	public function setValuesFromFormulier() {
		//object Boek vullen
		foreach ($this->getFormulier()->getFields() as $field) {
			if ($field instanceof InputField) {
				$this->setValue($field->getName(), $field->getValue());
			}
		}
	}

	/**
	 * Set gegeven waardes in Boek
	 *
	 * @param string $key moet bekend zijn, anders exception
	 * @param        $value
	 * @param bool   $initboek
	 * @throws Exception
	 * @return void
	 */
	public function setValue($key, $value, $initboek = false) {
		//$key voor leners en opmerkingen eerst opsplitsen
		if (substr($key, 0, 6) == 'lener_') {
			$exemplaarid = substr($key, 6);
			$key = 'lener';
		} elseif (substr($key, 0, 10) == 'opmerking_') {
			$exemplaarid = substr($key, 10);
			$key = 'opmerking';
		}

		switch ($key) {
			//integers
			case 'id':
			case 'uitgavejaar':
			case 'paginas':
				$this->$key = (int) trim($value);
				break;
			//strings
			case 'categorie':
				$this->rubriek = new Rubriek(explode(' - ', $value));
				break;
			case 'categorie_id':
			case 'rubriek':
				try {
					$this->rubriek = new Rubriek($value);
				} catch (Exception $e) {
					if ($initboek) {
						$this->rubriek = new Rubriek(1002);
					} else {
						throw new Exception($e->getMessage() . ' Boek::setValue "' . $key . '"');
					}
				}
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'code':
			case 'isbn':
			case 'status':
			case 'auteur':
				$this->$key = trim($value);
				break;
			case 'biebboek':
				$this->biebboek = $value;
				break;
			case 'lener':
				$this->exemplaren[$exemplaarid]['uitgeleend_uid'] = $value;
				break;
			case 'opmerking':
				$this->exemplaren[$exemplaarid]['opmerking'] = $value;
				break;
			default:
				throw new Exception('Veld [' . $key . '] is niet toegestaan Boek::setValue()');
		}
	}

}

class NieuwBoek extends Boek {

	public function __construct() {
		$this->id = 0;
		//zetten we de defaultwaarden voor het nieuwe boek.
		$this->rubriek = new Rubriek(108);
		if ($this->isBASFCie()) {
			$this->biebboek = 'ja';
		}
		$this->createBoekformulier();
	}

	public function createBoekformulier() {
		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if ($this->magBekijken()) {
			$nieuwboekformulier['boekgeg'] = new Subkopje('Boekgegevens:');
			$nieuwboekformulier = $nieuwboekformulier + $this->getCommonFields();
			if ($this->isBASFCie()) {
				$nieuwboekformulier['biebboek'] = new SelectField('biebboek', $this->biebboek, 'Is een biebboek?', array('ja' => 'C.S.R. boek', 'nee' => 'Eigen boek'));
			}
			$nieuwboekformulier[] = new FormKnoppen('/communicatie/bibliotheek/');

			$this->formulier = new Formulier(null, 'boekaddForm', '/communicatie/bibliotheek/nieuwboek/0');
			$this->formulier->addFields($nieuwboekformulier);
		}
	}

	/**
	 * waarden uit nieuw boek formulier opslaan
	 *
	 * @return bool
	 */
	public function saveFormulier() {
		$this->setValuesFromFormulier();
		//object Boek opslaan
		return $this->save();
	}

	/**
	 * Voeg het object Boek toe aan de db
	 *
	 * @return bool
	 */
	public function save() {

		$db = MijnSqli::instance();
		$qSave = "
			INSERT INTO biebboek (
				titel, auteur, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
			) VALUES (
				'" . $db->escape($this->getTitel()) . "',
				'" . $db->escape($this->getAuteur()) . "',
				" . (int) $this->getRubriek()->getId() . ",
				" . (int) $this->getUitgavejaar() . ",
				'" . $db->escape($this->getUitgeverij()) . "',
				" . (int) $this->getPaginas() . ",
				'" . $db->escape($this->getTaal()) . "',
				'" . $db->escape($this->getISBN()) . "',
				'" . $db->escape($this->getCode()) . "'
			);";
		if ($db->query($qSave)) {
			//id ook opslaan in object Boek.
			$this->id = $db->insert_id();
			if ($this->biebboek == 'ja') {
				$eigenaar = 'x222'; //C.S.R.Bieb is eigenaar
			} else {
				$eigenaar = LoginModel::getUid();
			}
			return $this->addExemplaar($eigenaar);
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::save()';
		return false;
	}

}

class BewerkBoek extends Boek {

	/** @var  Formulier */
	public $ajaxformuliervelden;  // Form objecten info v. boek
	/** @var Beschrijving[] */
	protected $beschrijvingen = array();
	protected $editbeschrijving;  // id van beschrijving die toegevoegd/bewerkt/verwijderd wordt

	public function __construct($init, $beschrijvingid) {
		parent::__construct($init);
		$this->editbeschrijving = $beschrijvingid;

		$this->createBoekformulier();

		$this->loadBeschrijvingen();
		$this->getEditBeschrijving()->setEditFlag();
		$this->createBeschrijvingformulier();
	}

	/*	 * **************************
	 * Ajax formuliervelden		*
	 * ************************** */

	/**
	 * maakt objecten voor de bewerkbare velden van een boek
	 */
	public function createBoekformulier() {
		$ajaxformuliervelden = array();
		//Eigenaar een exemplaar v.h. boek mag alleen bewerken
		if ($this->isEigenaar()) {
			$ajaxformuliervelden = $this->getCommonFields('Boek');
		}

		//voor eigenaars een veldje maken om boek uit te lenen.
		if ($this->exemplaren === null) {
			$this->loadExemplaren();
		}
		if (count($this->exemplaren) > 0) {
			foreach ($this->exemplaren as $exemplaar) {//id, eigenaar_uid, uitgeleend_uid, toegevoegd, status, uitleendatum
				if ($this->isEigenaar($exemplaar['id'])) {
					$ajaxformuliervelden['lener_' . $exemplaar['id']] = new RequiredLidField('lener_' . $exemplaar['id'], $exemplaar['uitgeleend_uid'], 'Uitgeleend aan', 'alleleden');
					$ajaxformuliervelden['opmerking_' . $exemplaar['id']] = new TextareaField('opmerking_' . $exemplaar['id'], $exemplaar['opmerking'], 'Opmerking over exemplaar');
				}
			}
		}
		$this->ajaxformuliervelden = new Formulier(null, '', '');
		$this->ajaxformuliervelden->addFields($ajaxformuliervelden);
	}

	/**
	 * Set gegeven waardes in Boek
	 *
	 * @param string $key moet bekend zijn, anders exception
	 * @param        $value
	 * @param bool   $initboek
	 * @throws Exception
	 * @return void
	 */
	public function setValue($key, $value, $initboek = false) {
		switch ($key) {
			case 'beschrijving':
				$this->getEditBeschrijving()->setTekst($value);
				break;
			default:
				parent::setValue($key, $value, $initboek);
		}
	}

	/**
	 * Geeft één veldobject $entry terug
	 *
	 * @param string $entry
	 * @throws Exception
	 * @return InputField
	 */
	public function getField($entry) {
		if (!$field = $this->ajaxformuliervelden->findByName($entry)) {
			throw new Exception('Dit formulier bevat geen veld "' . $entry . '"');
		}
		return $field;
	}

	/**
	 * Controleren of het gevraagde veld $entry correct is
	 *
	 * @param string $entry
	 * @return bool
	 */
	public function validField($entry) {
		//we checken alleen de TextFields, niet de comments enzo.
		$field = $this->getField($entry);
		return $field instanceof InputField AND $field->validate();
	}

	/**
	 * Slaat één veld $entry op in db
	 *
	 * @param string $entry eigenschapnaam, waarbij leners en opmerkingen ook exemplaarid bevatten
	 * @return bool
	 */
	public function saveField($entry) {
		//waarde van $entry in Boek invullen
		$field = $this->getField($entry);
		if ($field instanceof InputField) {
			$this->setValue($field->getName(), $field->getValue());
		} else {
			$this->error .= 'saveField(): ' . $entry . ' Geen instanceof TextField.';
			return false;
		}
		//waarde van $entry uit Boek opslaan
		if ($this->saveProperty($entry)) {
			return true;
		} else {
			$this->error .= 'saveField(): saveProperty mislukt. ';
		}
		return false;
	}

	/**
	 * Opslaan van waarde van een bewerkbaar veld in db
	 *
	 * @param string $entry eigenschapnaam, waarbij leners en opmerkingen ook exemplaarid bevatten
	 * @return bool
	 */
	public function saveProperty($entry) {
		$db = MijnSqli::instance();
		$key = $entry; //op een enkele uitzondering na
		$table = "biebboek";
		$id = $this->getId();

		//$entry voor leners en opmerkingen eerst opsplitsen
		if (substr($entry, 0, 6) == 'lener_') {
			$exemplaarid = substr($entry, 6);
			$entry = 'lener';
		} elseif (substr($entry, 0, 10) == 'opmerking_') {
			$exemplaarid = substr($entry, 10);
			$entry = 'opmerking';
		}

		switch ($entry) {
			case 'rubriek':
				$value = (int) $this->getRubriek()->getId();
				$key = "categorie_id";
				break;
			case 'uitgavejaar':
			case 'paginas':
				$value = (int) $this->$entry;
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'isbn':
			case 'code':
			case 'auteur':
				$value = "'" . $db->escape($this->$entry) . "'";
				break;
			case 'lener':
				return $this->leenExemplaar($exemplaarid, $this->exemplaren[$exemplaarid]['uitgeleend_uid']);
			case 'opmerking':
				$table = "biebexemplaar";
				$key = "opmerking";
				$value = "'" . $db->escape($this->exemplaren[$exemplaarid]['opmerking']) . "'";
				$id = (int) $exemplaarid;
				break;
			default:
				$this->error .= 'Veld [' . $entry . '] is niet toegestaan Boek::saveProperty()';
				return false;
		}

		$qSave = "
			UPDATE " . $table . " SET
				" . $key . "= " . $value . "
			WHERE id= " . $id . "
			LIMIT 1;";
		if ($db->query($qSave)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::saveProperty()';
		return false;
	}

	/**
	 * Retourneert strings.
	 *
	 * @param $entry string eigenschapnaam, waarbij leners en opmerkingen ook exemplaarid bevatten 
	 * @return string waarde zoals in object opgeslagen
	 */
	public function getProperty($entry) {
		//$entry voor leners eerst opsplitsen
		if (substr($entry, 0, 6) == 'lener_') {
			$exemplaarid = substr($entry, 6);
			$entry = 'lener';
		} elseif (substr($entry, 0, 10) == 'opmerking_') {
			$exemplaarid = substr($entry, 10);
			$entry = 'opmerking';
		}

		switch ($entry) {
			case 'rubriek':
			case 'rubriekid':
				$return = $this->getRubriek()->getId();
				break;
			case 'titel':
			case 'uitgavejaar':
			case 'uitgeverij':
			case 'paginas':
			case 'taal':
			case 'isbn':
			case 'code':
			case 'auteur':
				$return = $this->$entry;
				break;
			case 'lener':
				$uid = $this->exemplaren[$exemplaarid]['uitgeleend_uid'];
				$naam = Lid::naamLink($uid, 'full', 'plain');
				if ($naam !== false) {
					$return = $naam;
				} else {
					$return = 'Geen geldig lid getProperty()';
				}
				break;
			case 'opmerking':
				$return = $this->exemplaren[$exemplaarid]['opmerking'];
				break;
			default:
				return 'entry "' . $entry . '" is niet toegestaan. Boek::getProperty()';
		}
		return htmlspecialchars($return);
	}

	/*	 * ************
	 * Exemplaren *
	 * ************ */

	/**
	 * Slaat op dat een exemplaar is geleend
	 *
	 * @param int 		  	$exemplaarid wordt status 'uitgeleend' in db
	 * @param null|string 	$lener uid
	 * @return bool true geslaagd
	 *              false mislukt
	 */
	public function leenExemplaar($exemplaarid, $lener = null) {
		//alleen status beschikbaar toegestaan, of je moet eigenaar zijn die iemand toevoegd (tbv editable fields)
		if ($this->getStatusExemplaar($exemplaarid) != 'beschikbaar') {
			$this->error .= 'Boek is niet beschikbaar. leenExemplaar()';
			return false;
		}
		if ($lener == null) {
			$lener = LoginModel::getUid();
		}

		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				uitgeleend_uid = '" . $db->escape($lener) . "',
				status = 'uitgeleend',
				uitleendatum = '" . getDateTime() . "',
				leningen=leningen +1
			WHERE id = " . (int) $exemplaarid . "
			LIMIT 1;";
		if ($db->query($query)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::leenExemplaar()';
		return false;
	}

	/**
	 * Slaat op dat een exemplaar iemand exemplaar teruggeeft
	 * 
	 * @param int $exemplaarid wordt status 'terugegeven' in db
	 * @return bool	true geslaagd
	 * 			    false mislukt
	 */
	public function teruggevenExemplaar($exemplaarid) {
		if ($this->getStatusExemplaar($exemplaarid) != 'uitgeleend') {
			$this->error .= 'Boek is niet uitgeleend. ';
			return false;
		}

		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				status = 'teruggegeven'
			WHERE id = " . (int) $exemplaarid . "
			LIMIT 1;";
		if ($db->query($query)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::teruggegevenExemplaar()';
		return false;
	}

	/**
	 * Slaat op dat een exemplaar iemand exemplaar heeft ontvangen
	 * 
	 * @param int $exemplaarid wordt status 'beschikbaar' in db
	 * @return bool true geslaagd
	 * 			    false mislukt
	 */
	public function terugontvangenExemplaar($exemplaarid) {
		if (!in_array($this->getStatusExemplaar($exemplaarid), array('uitgeleend', 'teruggegeven'))) {
			$this->error .= 'Boek is niet uitgeleend. ';
			return false;
		}
		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				uitgeleend_uid = '',
				status = 'beschikbaar'
			WHERE id = " . (int) $exemplaarid . "
			LIMIT 1;";
		if ($db->query($query)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::terugontvangenExemplaar()';
		return false;
	}

	/**
	 * Markeert exemplaar als vermist
	 * 
	 * @param int $exemplaarid wordt status 'vermist' in db
	 * @return bool true gelukt
	 * 			false mislukt
	 */
	public function vermistExemplaar($exemplaarid) {
		if ($this->getStatusExemplaar($exemplaarid) == 'vermist') {
			$this->error .= 'Boek is al vermist. ';
			return false;
		} elseif ($this->getStatusExemplaar($exemplaarid) != 'beschikbaar') {
			$this->error .= 'Boek is nog uitgeleend. ';
			return false;
		}

		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				status = 'vermist',
				uitleendatum = '" . getDateTime() . "'
			WHERE id = " . (int) $exemplaarid . "
			LIMIT 1;";
		if ($db->query($query)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::vermistExemplaar()';
		return false;
	}

	/**
	 * Markeert exemplaar als beschikbaar
	 * 
	 * @param int $exemplaarid wordt status 'beschikbaar' in db
	 * @return bool	true gelukt
	 * 				false mislukt
	 */
	public function gevondenExemplaar($exemplaarid) {
		if ($this->getStatusExemplaar($exemplaarid) != 'vermist') {
			$this->error .= 'Boek is niet vermist gemeld. ';
			return false;
		}

		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				status = 'beschikbaar'
			WHERE id = " . (int) $exemplaarid . "
			LIMIT 1;";
		if ($db->query($query)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::gevondenExemplaar()';
		return false;
	}

	/*	 * ******************************
	 * Boekrecensies/beschrijvingen *
	 * ****************************** */

	/**
	 * maakt objecten van formulier om beschrijving toe te voegen of te bewerken
	 */
	public function createBeschrijvingformulier() {
		if ($this->magBekijken()) {
			$schrijver = '';
			$annuleer = null;
			$posturl = '/communicatie/bibliotheek/bewerkbeschrijving/' . $this->getId();

			if ($this->editbeschrijving == 0) {
				$titeltekst = 'Geef uw beschrijving of recensie van het boek:';
			} else {
				$titeltekst = 'Bewerk uw beschrijving of recensie van het boek:';
				$uid = $this->getEditBeschrijving()->getSchrijver();
				$naam = Lid::naamLink($uid, 'full', 'plain');
				if ($naam !== false) {
					$schrijver = $naam . ':';
				}
				$annuleer = '/communicatie/bibliotheek/boek/' . $this->getId();
				$posturl .= '/' . $this->editbeschrijving;
			}
			$boekbeschrijvingform[] = new Subkopje($titeltekst);
			$textfield = new RequiredUbbPreviewField('beschrijving', $this->getEditBeschrijving()->getTekst(), $schrijver, true, 255);
			$boekbeschrijvingform[] = $textfield;
			$boekbeschrijvingform[] = new FormKnoppen($annuleer);

			$this->formulier = new Formulier(null, 'Beschrijvingsformulier', $posturl);
			$this->formulier->addFields($boekbeschrijvingform);
		}
	}

	/**
	 * laad beschrijvingen van dit boek, inclusief Beschrijving(0) indien nodig.
	 */
	protected function loadBeschrijvingen() {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, boek_id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
			FROM biebbeschrijving
			WHERE boek_id=" . (int) $this->getId() . "
			ORDER BY toegevoegd;";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			while ($beschrijving = $db->next($result)) {
				$this->beschrijvingen[$beschrijving['id']] = new Beschrijving($beschrijving);
			}
		} else {
			$this->error .= $db->error();
		}
		//als er een nieuwe beschrijving toegevoegd kan worden is een leeg object nodig 
		if ($this->editbeschrijving == 0) {
			$this->beschrijvingen[0] = new Beschrijving(0, $this->getId());
		}
	}

	/**
	 * Geeft array met beschrijvingen van dit boek
	 *
	 * @return array Beschrijving[]
	 */
	public function getBeschrijvingen() {
		return $this->beschrijvingen;
	}

	/**
	 * Aantal beschrijvingen
	 *
	 * @return int
	 */
	public function countBeschrijvingen() {
		return count($this->beschrijvingen);
	}

	/**
	 * Geeft Beschrijving-object dat bewerkt/toegevoegd/verwijdert wordt
	 *
	 * @return Beschrijving
	 * @throws Exception
	 */
	public function getEditBeschrijving() {
		if (array_key_exists($this->editbeschrijving, $this->beschrijvingen)) {
			return $this->beschrijvingen[$this->editbeschrijving];
		} else {
			throw new Exception('Beschrijving niet bij dit boek gevonden! Boek::getEditBeschrijving() mislukt. ');
		}
	}

	/**
	 * controleert rechten voor bewerkactie
	 *
	 * @param null|int	id van een beschrijving
	 * 					of null: in Boek geladen beschrijving wordt bekeken
	 * @return bool
	 * 		een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */
	public function magBeschrijvingVerwijderen($beschrijvingsid = null) {
		if ($this->magVerwijderen()) {
			return true;
		}
		if ($beschrijvingsid === null) {
			$beschrijvingsid = $this->editbeschrijving;
		}
		return $this->beschrijvingen[$beschrijvingsid]->isSchrijver();
	}

	/**
	 * verwijdert in Boek geladen beschrijving
	 */
	public function verwijderBeschrijving() {
		return $this->getEditBeschrijving()->verwijder();
	}

	/**
	 * Plaatst gegevens in geladen object Beschrijving en slaat beschrijving op
	 *
	 * @return bool
	 */
	public function saveFormulier() {
		$this->setValuesFromFormulier();
		//de beschrijving/recensie opslaan
		return $this->getEditBeschrijving()->save();
	}

}

class TitelField extends RequiredTextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (Catalogus::existsProperty('titel', $this->getValue())) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
