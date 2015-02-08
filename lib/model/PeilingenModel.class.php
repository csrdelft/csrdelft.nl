<?php

/**
 * PeilingenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * 
 * Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
 * 
 */
class PeilingenModel {

	private $id = 0;
	private $titel = '';
	private $tekst = '';
	private $totaal = 0; //totaal aantal stemmen voor deze peiling.
	private $opties = array();
	private $hasvoted = false;

	function __construct($init) {
		$init = (int) $init;
		if ($init != 0) {
			$this->load($init);
		} else {
			//nieuwe peiling
		}
	}

	public function load($id) {
		$this->id = (int) $id;

		$sPeilingQuery = "
			SELECT
				id, titel, tekst, (
					SELECT uid FROM peiling_stemmen
					WHERE peilingid=" . $this->getId() . " AND uid='" . LoginModel::getUid() . "'
					LIMIT 1) as has_voted
			FROM peiling
			WHERE peiling.id = " . $this->getId() . ';';

		$db = MijnSqli::instance();
		$rPeiling = $db->query($sPeilingQuery);

		if ($db->numRows($rPeiling) == 1) {
			$data = $db->next($rPeiling);
			$this->titel = $data['titel'];
			$this->tekst = $data['tekst'];
			$this->hasvoted = $data['has_voted'];
		} else {
			throw new Exception('Peiling bestaat niet met (id:' . $this->getId() . ')');
		}

		$this->loadOptions();
	}

	public function loadOptions() {
		$optiesQuery = "
			SELECT id, optie, stemmen
			FROM peilingoptie
			WHERE peilingid=" . $this->getId() . "
			ORDER BY id ASC;";
		$db = MijnSqli::instance();
		$opties = $db->query2array($optiesQuery);

		if (!is_array($opties)) {
			$opties = array();
		}
		//reken totaal aantal stemmen uit.
		foreach ($opties as $optie) {
			$this->totaal+=$optie['stemmen'];
		}

		foreach ($opties as $optie) {
			if ($this->totaal == 0) {
				$optie['percentage'] = 0;
			} else {
				$optie['percentage'] = ($optie['stemmen'] / $this->totaal) * 100;
			}
			$this->opties[] = $optie;
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getTekst() {
		return $this->tekst;
	}

	public function hasVoted() {
		return $this->hasvoted;
	}

	public function getOpties() {
		return $this->opties;
	}

	public function getStemmenAantal() {
		return $this->totaal;
	}

//wth is dit voor methodenaam

	public function magStemmen() {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		return $this->hasVoted() == '';
	}

	public function stem($optieId) {
		if ($this->magStemmen()) {
			return $this->addStem((int) $optieId);
		}
		return false;
	}

	private function addStem($optieId) {
		$db = MijnSqli::instance();

		$updateOptie = "
			UPDATE peilingoptie
			SET stemmen = stemmen + 1
			WHERE id=" . $optieId . ';';
		$logStem = "
			INSERT INTO peiling_stemmen (peilingid, uid) VALUES
			(" . $this->getId() . ",'" . LoginModel::getUid() . "');";

		return $db->query($updateOptie) AND $db->query($logStem);
	}

	public function deletePeiling() {
		if ($this->magBewerken()) {
			return $this->delete();
		}
		return 0;
	}

	private function delete() {

		$db = MijnSqli::instance();

		$sDelete = "DELETE FROM `peiling` WHERE `id`=" . $this->getId() . ";";
		$sDeleteOpties = " DELETE FROM  `peilingoptie` WHERE `peilingid`=" . $this->getId() . ";";
		$sDeleteLog = "DELETE FROM `peiling_stemmen` WHERE `peilingid`=" . $this->getId() . ";";

		return $db->query($sDelete) AND $db->query($sDeleteOpties) AND $db->query($sDeleteLog);
	}

	public static function maakPeiling($properties) {
		if (PeilingenModel::magBewerken() && is_array($properties)) {
			return new PeilingenModel(PeilingenModel::create($properties));
		}
		return 0;
	}

	//INSERT INTO `peiling` (`id`,`titel`,`tekst`) VALUES (NULL,'titel','verhaal')
	//INSERT INTO `peilingoptie` (`id`,`peilingid`,`optie`,`stemmen`) VALUES (NULL,pid,'optietekst',0)
	//Geeft het id van de nieuwe peiling terug, of NULL.
	private static function create($properties) {
		$titel = $properties['titel'];
		$verhaal = $properties['verhaal'];
		$opties = array();

		if (is_array($properties['opties'])) {
			$opties = $properties['opties'];
		}
		$db = MijnSqli::instance();

		$sCreate = "
			INSERT INTO `peiling` (`titel`,`tekst`) VALUES
				('" . $db->escape($titel) . "','" . $db->escape($verhaal) . "');";
		$r = $db->query($sCreate);
		if (!$r) {
			return NULL;
		}
		$pid = $db->insert_id();

		foreach ($opties as $optie) {
			$sCreateOptie = "
				INSERT INTO
					`peilingoptie`
					(`peilingid`,`optie`,`stemmen`)
				VALUES
					(" . $pid . ",'" . $db->escape($optie) . "',0)
				";
			$r = $db->query($sCreateOptie);
		}
		return $pid;
	}

	public static function magBewerken() {
		//Elk BASFCie-lid heeft voorlopig peilingbeheerrechten.
		return LoginModel::mag('P_ADMIN,groep:bestuur,groep:BASFCie');
	}

	public static function getLijst() {
		$sSelectQuery = "
			SELECT *
			FROM peiling
			ORDER BY id DESC";

		$sSelectQuery .= ';';
		$db = MijnSqli::instance();
		$rPeilingen = $db->query($sSelectQuery);
		return $db->result2array($rPeilingen);
	}

}
