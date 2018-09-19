<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\RequiredBBCodeField;
use CsrDelft\view\formulier\invoervelden\RequiredLidField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class BewerkBoek extends BiebBoek {

	/** @var  Formulier */
	public $ajaxformuliervelden;  // Form objecten info v. boek
	/** @var BiebBeschrijving[] */
	protected $beschrijvingen = array();
	protected $editbeschrijving;  // id van beschrijving die toegevoegd/bewerkt/verwijderd wordt

	public function __construct(
		$init,
		$beschrijvingid
	) {
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
		$this->ajaxformuliervelden = new BiebFormulier();
		$this->ajaxformuliervelden->addFields($ajaxformuliervelden);
	}

	/**
	 * Set gegeven waardes in Boek
	 *
	 * @param string $key moet bekend zijn, anders exception
	 * @param        $value
	 * @param bool $initboek
	 *
	 * @return void
	 */
	public function setValue(
		$key,
		$value,
		$initboek = false
	) {
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
	 *
	 * @throws CsrException
	 * @return InputField
	 */
	public function getField($entry) {
		if (!$field = $this->ajaxformuliervelden->findByName($entry)) {
			throw new CsrException('Dit formulier bevat geen veld "' . $entry . '"');
		}
		return $field;
	}

	/**
	 * Controleren of het gevraagde veld $entry correct is
	 *
	 * @param string $entry
	 *
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
	 *
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
	 *
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
				$value = (int)$this->getRubriek()->getId();
				$key = "categorie_id";
				break;
			case 'uitgavejaar':
			case 'paginas':
				$value = (int)$this->$entry;
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
				$id = (int)$exemplaarid;
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
	 *
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
				$naam = ProfielModel::getNaam($uid, 'volledig');
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
	 * @param int $exemplaarid wordt status 'uitgeleend' in db
	 * @param null|string $lener uid
	 *
	 * @return bool true geslaagd
	 *              false mislukt
	 */
	public function leenExemplaar(
		$exemplaarid,
		$lener = null
	) {
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
			WHERE id = " . (int)$exemplaarid . "
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
	 *
	 * @return bool    true geslaagd
	 *                false mislukt
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
			WHERE id = " . (int)$exemplaarid . "
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
	 *
	 * @return bool true geslaagd
	 *                false mislukt
	 */
	public function terugontvangenExemplaar($exemplaarid) {
		if (!in_array($this->getStatusExemplaar($exemplaarid), array('uitgeleend', 'teruggegeven'))) {
			$this->error .= 'Boek is niet uitgeleend. ';
			return false;
		}
		$db = MijnSqli::instance();
		$query = "
			UPDATE biebexemplaar SET
				uitgeleend_uid = NULL,
				status = 'beschikbaar'
			WHERE id = " . (int)$exemplaarid . "
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
	 *
	 * @return bool true gelukt
	 *            false mislukt
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
			WHERE id = " . (int)$exemplaarid . "
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
	 *
	 * @return bool    true gelukt
	 *                false mislukt
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
			WHERE id = " . (int)$exemplaarid . "
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
			$posturl = '/bibliotheek/bewerkbeschrijving/' . $this->getId();

			if ($this->editbeschrijving == 0) {
				$titeltekst = 'Geef uw beschrijving of recensie van het boek:';
			} else {
				$titeltekst = 'Bewerk uw beschrijving of recensie van het boek:';
				$uid = $this->getEditBeschrijving()->getSchrijver();
				$naam = ProfielModel::getNaam($uid, 'volledig');
				if ($naam !== false) {
					$schrijver = $naam . ':';
				}
				$annuleer = '/bibliotheek/boek/' . $this->getId();
				$posturl .= '/' . $this->editbeschrijving;
			}
			$boekbeschrijvingform[] = new Subkopje($titeltekst);
			$textfield = new RequiredBBCodeField('beschrijving', $this->getEditBeschrijving()->getTekst(), $schrijver, true, 255);
			$boekbeschrijvingform[] = $textfield;
			$boekbeschrijvingform[] = new FormDefaultKnoppen($annuleer);

			$this->formulier = new Formulier(null, $posturl);
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
			WHERE boek_id=" . (int)$this->getId() . "
			ORDER BY toegevoegd;";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			while ($beschrijving = $db->next($result)) {
				$this->beschrijvingen[$beschrijving['id']] = new BiebBeschrijving($beschrijving);
			}
		} else {
			$this->error .= $db->error();
		}
		//als er een nieuwe beschrijving toegevoegd kan worden is een leeg object nodig
		if ($this->editbeschrijving == 0) {
			$this->beschrijvingen[0] = new BiebBeschrijving(0, $this->getId());
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
	 * @return BiebBeschrijving
	 * @throws CsrException
	 */
	public function getEditBeschrijving() {
		if (array_key_exists($this->editbeschrijving, $this->beschrijvingen)) {
			return $this->beschrijvingen[$this->editbeschrijving];
		} else {
			throw new CsrException('Beschrijving niet bij dit boek gevonden! Boek::getEditBeschrijving() mislukt. ');
		}
	}

	/**
	 * controleert rechten voor bewerkactie
	 *
	 * @param null|int id van een beschrijving
	 *                    of null: in Boek geladen beschrijving wordt bekeken
	 *
	 * @return bool
	 *        een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
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
