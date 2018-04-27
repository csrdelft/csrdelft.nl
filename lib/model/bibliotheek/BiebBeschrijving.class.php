<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\MijnSqli;
use CsrDelft\model\security\LoginModel;

/**
 * BiebBeschrijving.class.php  |  Gerrit Uitslag
 *
 * een boekbeschrijving of boekrecensie
 *
 */
class BiebBeschrijving {

	private $id = 0;  // id van recensie
	private $beschrijving = array();

	public function __construct($init, $boekid = null) {
		$this->load($init, $boekid);
	}

	private function load($init, $boekid) {
		if (is_array($init)) {
			$this->id = $init['id'];
			$this->beschrijving = $init;
		} else {
			$this->id = (int)$init;
			if ($this->getId() == 0) {
				$this->beschrijving = array('id' => 0, 'beschrijving' => '', 'boek_id' => (int)$boekid, 'schrijver_uid' => LoginModel::getUid());
			} else {
				$db = MijnSqli::instance();
				$query = "
					SELECT id, boek_id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
					FROM biebbeschrijving
					WHERE id=" . (int)$this->id . "
					LIMIT 1;";
				$result = $db->query($query);

				if ($db->numRows($result) > 0) {
					$beschrijving = $db->next($result);
					$this->beschrijving = $beschrijving;
				} else {
					throw new CsrException('Mislukt. Boek::getBeschrijving()' . $db->error());
				}
			}
		}
	}

	public function getTekst() {
		return $this->beschrijving['beschrijving'];
	}

	public function getSchrijver() {
		return $this->beschrijving['schrijver_uid'];
	}

	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function getId() {
		return $this->id;
	}

	public function setTekst($tekst) {
		$this->beschrijving['beschrijving'] = $tekst;
	}

	public function setEditFlag() {
		$this->beschrijving['bewerk'] = true;
	}

	/*
	 * @param 	$uid lidnummer of null
	 * @return	bool
	 * 		een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */

	public function isSchrijver($uid = null) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		if ($uid === null) {
			$uid = LoginModel::getUid();
		}
		return $this->beschrijving['schrijver_uid'] == $uid;
	}

	/*
	 * Sla boekrecensie/beschrijving op
	 */

	public function save() {
		$db = MijnSqli::instance();
		if ($this->getId() == 0) {
			$qSave = "
				INSERT INTO biebbeschrijving (
					boek_id, schrijver_uid, beschrijving, toegevoegd
				) VALUES (
					" . (int)$this->beschrijving['boek_id'] . ",
					'" . $db->escape(LoginModel::getUid()) . "',
					'" . $db->escape($this->getTekst()) . "',
					'" . getDateTime() . "'
				);";
		} else {
			$qSave = "
				UPDATE biebbeschrijving SET
					beschrijving= '" . $db->escape($this->getTekst()) . "',
					bewerkdatum='" . getDateTime() . "'
				WHERE id= " . $this->getId() . "
				LIMIT 1;";
		}
		if ($db->query($qSave)) {
			$this->id = $db->insert_id(); //id van beschrijving weer tijdelijk opslaan, zodat we beschrijving kunnen linken
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::saveBeschrijving()';
		return false;
	}

	/*
	 * verwijder beschrijving
	 *
	 * @return	true geslaagd
	 * 			false mislukt, iig id=0 is false
	 */

	public function verwijder() {
		$db = MijnSqli::instance();
		$qVerwijderBeschrijving = "DELETE FROM biebbeschrijving WHERE id=" . (int)$this->getId() . " LIMIT 1;";
		return $db->query($qVerwijderBeschrijving);
	}

}
