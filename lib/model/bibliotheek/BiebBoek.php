<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\MijnSqli;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;

/**
 * BiebBoek.php  |  Gerrit Uitslag
 *
 * boeken
 *
 */
class BiebBoek {

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
	 * @throws CsrException
	 */
	private function load($init = 0) {
		if (is_array($init)) {
			$this->array2properties($init);
		} else {
			$this->id = (int)$init;
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
					throw new CsrException('load() mislukt. Bestaat het boek wel? ' . $db->error());
				}
			} else {
				throw new CsrException('load() mislukt. Boekid = 0');
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
	 * @return BiebRubriek
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
		return CSR_ROOT . '/bibliotheek/boek/' . $this->getId();
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
			$where = "WHERE boek_id =" . (int)$this->getId();
		} else {
			$where = "WHERE id =" . (int)$exemplaarid;
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
	 * @return  bool
	 *    boek mag alleen door admins verwijdert worden
	 */
	public function magVerwijderen() {
		return LoginModel::mag('commissie:BASFCie,P_BIEB_MOD,P_ADMIN');
	}

	/**
	 * Controleert rechten voor bewerkactie
	 *
	 * @return  bool
	 *    boek mag alleen door admins of door eigenaar v.e. exemplaar bewerkt worden
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
	 *  - BASFCieleden zijn eigenaar van boeken van de bibliotheek
	 *
	 * @param null|int geen of $exemplaarid integer
	 * @return bool true
	 *        of ingelogd eigenaar is v.e. exemplaar van het boek
	 *        of van het specifieke exemplaar als exemplaarid is gegeven.
	 *      false
	 *        geen geen resultaat of niet de eigenaar
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
		return LoginModel::mag('commissie:BASFCie');
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
			WHERE id=" . (int)$exemplaarid . ";";
		$result = $db->query($qLener);
		if ($db->numRows($result) > 0) {
			$lener = $db->next($result);
			return $lener['uitgeleend_uid'] == LoginModel::getUid();
		} else {
			$this->error .= $db->error();
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
			WHERE boek_id=" . (int)$this->getId() . "
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
	 *      of anders lege string
	 */
	public function getStatusExemplaar($exemplaarid) {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, status
			FROM biebexemplaar
			WHERE id=" . (int)$exemplaarid . ";";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			$exemplaar = $db->next($result);
			return $exemplaar['status'];
		} else {
			$this->error .= $db->error();
			return '';
		}
	}

	/**
	 * Voeg exemplaar toe
	 *
	 * @param string $eigenaar uid
	 * @return bool true geslaagd
	 *      false  mislukt
	 *          $eigenaar is ongeldig uid
	 */
	public function addExemplaar($eigenaar) {
		if (!AccountModel::isValidUid($eigenaar)) {
			return false;
		}
		$db = MijnSqli::instance();
		$qSave = "
			INSERT INTO biebexemplaar (
				boek_id, eigenaar_uid, toegevoegd, status
			) VALUES (
				" . (int)$this->getId() . ",
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
	 * @return bool  true geslaagd
	 *      false mislukt
	 */
	public function verwijderExemplaar($id) {
		$db = MijnSqli::instance();
		$qDeleteExemplaar = "DELETE FROM biebexemplaar WHERE id=" . (int)$id . " LIMIT 1;";
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
		$fields['auteur']->suggestions[] = '/bibliotheek/autocomplete/auteur?q=';
		$fields['auteur']->placeholder = 'Achternaam, Voornaam V.L. van de';
		$fields['paginas'] = new IntField('paginas', $this->getPaginas(), "Pagina's", 0, 10000);
		$fields['taal'] = new TextField('taal', $this->getTaal(), 'Taal', 25);
		$fields['taal']->suggestions[] = '/bibliotheek/autocomplete/taal?q=';
		$fields['isbn'] = new TextField('isbn', $this->getISBN(), 'ISBN', 15);
		$fields['isbn']->placeholder = 'Uniek nummer';
		$fields['uitgeverij'] = new TextField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij', 100);
		$fields['uitgeverij']->suggestions[] = '/bibliotheek/autocomplete/uitgeverij?q=';
		$fields['uitgavejaar'] = new IntField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar', 0, 2100);
		$fields['rubriek'] = new SelectField('rubriek', $this->getRubriek()->getId(), 'Rubriek', BiebRubriek::getAllRubrieken($samenvoegen = true, $short = true));
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
		return $this->getFormulier()->validate();
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
	 * @param bool $initboek
	 * @throws CsrException
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
				$this->$key = (int)trim($value);
				break;
			//strings
			case 'categorie':
				$this->rubriek = new BiebRubriek(explode(' - ', $value));
				break;
			case 'categorie_id':
			case 'rubriek':
				try {
					$this->rubriek = new BiebRubriek($value);
				} catch (CsrException $e) {
					if ($initboek) {
						$this->rubriek = new BiebRubriek(1002);
					} else {
						throw new CsrException($e->getMessage() . ' Boek::setValue "' . $key . '"');
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
				throw new CsrGebruikerException('Veld [' . $key . '] is niet toegestaan Boek::setValue()');
		}
	}

}
