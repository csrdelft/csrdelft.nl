<?php

require_once 'MVC/model/entity/Bestand.class.php';

/**
 * document.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Bestanden worden allemaal in één map opgeslagen, met met hun documentID als prefix.
 *
 * Als men dus 2008-halfjaarschema.pdf upload komt er een bestand dat bijvoorbeeld
 * 1123_2008-halfjaarschema.pdf heet in de documentenmap te staan.
 *
 * In de database wordt de originele filename opgeslagen, zonder prefix dus.
 *
 */
class Document extends Bestand {

	private $ID = 0;
	private $naam;
	private $catID;   //CategorieID van de categorie van dit bestand
	private $categorie = null; //DocumentCategorie-object van dit bestand
	private $toegevoegd; //toevoegdatum
	private $eigenaar;  //uid van de eigenaar
	private $leesrechten = 'P_LEDEN_READ'; //rechten nodig om bestand te mogen bekijken en downloaden

	public function __construct($init) {
		$this->filesize = 0;  //bestandsafmeting in bytes
		$this->mimetype = 'application/octet-stream'; //mime-type van het bestand
		$this->load($init);
	}

	public function load($init = 0) {
		if (is_array($init)) {
			$this->array2properties($init);
		} else {
			$this->ID = (int) $init;
			if ($this->getID() == 0) {
				//Bij $this->ID==0 gaat het om een nieuw document. Hier
				//zetten we de defaultwaarden voor het nieuwe document.
				$this->setToegevoegd(getDateTime());
				$this->setEigenaar(LoginModel::getUid());
			} else {
				$db = MijnSqli::instance();
				$query = "
					SELECT ID, naam, catID, filename, filesize, mimetype, toegevoegd, eigenaar, leesrechten
					FROM document
					WHERE ID=" . $this->getID() . ";";
				$doc = $db->getRow($query);
				if (is_array($doc)) {
					$this->array2properties($doc);
				} else {
					throw new Exception('load() mislukt. Bestaat het document wel?');
				}
			}
		}
	}

	public function array2properties($array) {
		$properties = array('ID', 'naam', 'catID', 'filename', 'filesize', 'mimetype', 'toegevoegd', 'eigenaar', 'leesrechten');
		foreach ($properties as $prop) {
			if (!isset($array[$prop])) {
				throw new Exception('Documentproperties-array is niet compleet: ' . $prop . ' mist.');
			}
			$this->$prop = $array[$prop];
		}
	}

	public function save() {
		$db = MijnSqli::instance();
		if ($this->getID() == 0) {
			$query = "
				INSERT INTO document (
					naam, catID, filename, filesize, mimetype, toegevoegd, eigenaar, leesrechten
				)VALUES(
					'" . $db->escape($this->getNaam()) . "',
					" . $this->getCatID() . ",
					'" . $db->escape($this->getFileName()) . "',
					" . $this->getFileSize() . ",
					'" . $db->escape($this->getMimetype()) . "',
					'" . $this->getToegevoegd() . "',
					'" . $this->getEigenaar() . "',
					'" . $this->getLeesrechten() . "'
				);";
		} else {
			$query = "
				UPDATE document SET
					naam='" . $db->escape($this->getNaam()) . "',
					catID=" . $this->getCatID() . ",
					filename='" . $db->escape($this->getFileName()) . "',
					filesize=" . $this->getFileSize() . ",
					mimetype='" . $db->escape($this->getMimetype()) . "',
					toegevoegd='" . $this->getToegevoegd() . "',
					eigenaar='" . $this->getEigenaar() . "',
					leesrechten='" . $this->getLeesrechten() . "'
				WHERE ID=" . $this->getID() . ";";
		}
		if ($db->query($query)) {
			if ($this->getID() == 0) {
				$this->ID = $db->insert_id();
			}
			return true;
		}
		return false;
	}

	public function delete() {
		$deletequery = 'DELETE FROM document WHERE ID=' . $this->getID();
		//zorg dat $this->deleteFile geen exceptions gooit als er geen bestand bestaat
		//voor het huidige document, zodat verwijderen gewoon lukt.
		return $this->deleteFile(false) && MijnSqli::instance()->query($deletequery);
	}

	public function getID() {
		return $this->ID;
	}

	public function getNaam() {
		return $this->naam;
	}

	public function getCatID() {
		return $this->catID;
	}

	public function getCategorie($force = false) {
		if ($force OR $this->categorie == null) {
			$this->categerie = new DocumentCategorie($this->getCatID());
		}
	}

	public function getBestand() {
		$bestand = new Bestand();
		$bestand->directory = $this->getPath();
		$bestand->filename = $this->getFullFileName();
		$bestand->filesize = $this->getFileSize();
		$bestand->mimetype = $this->getMimetype();
		if (!$this->magBekijken()) {
			return false;
		}
		return $bestand;
	}

	public function getFileName() {
		return $this->filename;
	}

	public function hasFile() {
		if (!$this->magBekijken()) {
			return false;
		}
		return $this->getFileName() != '' AND file_exists($this->getFullPath());
	}

	public function getFileSize() {
		return $this->filesize;
	}

	public function getMimetype() {
		return $this->mimetype;
	}

	public function getToegevoegd() {
		return $this->toegevoegd;
	}

	public function getEigenaar() {
		return $this->eigenaar;
	}

	public function setNaam($naam) {
		$this->naam = $naam;
	}

	public function setCatID($catID) {
		$this->catID = (int) $catID;
	}

	public function setFileName($naam) {
		$this->filename = $naam;
	}

	public function setFileSize($filesize) {
		$this->filesize = (int) $filesize;
	}

	public function setMimetype($mime) {
		$this->mimetype = $mime;
	}

	public function setToegevoegd($toegevoegd) {
		$this->toegevoegd = $toegevoegd;
	}

	public function setEigenaar($uid) {
		if (!Lid::isValidUID($uid)) {
			throw new Exception('Geen geldig uid opgegeven');
		}
		$this->eigenaar = $uid;
	}

	public function isEigenaar($uid = null) {
		if ($uid == null) {
			LoginModel::getUid();
		}
		return $uid == $this->getEigenaar();
	}

	public function magBewerken() {
		return $this->isEigenaar() OR LoginModel::mag('P_DOCS_MOD');
	}

	public function getLeesrechten() {
		return $this->leesrechten;
	}

	public function magBekijken() {
		return LoginModel::mag($this->getLeesrechten());
	}

	public function magVerwijderen() {
		return LoginModel::mag('P_DOCS_MOD');
	}

	public function getFriendlyMimetype() {
		if (strpos($this->getMimetype(), 'pdf')) {
			return 'pdf';
		} elseif (strpos($this->getMimetype(), 'msword') OR strpos($this->getMimetype(), 'officedocument.word')) {
			return 'doc';
		} elseif (strpos($this->getMimetype(), 'officedocument.pres')) {
			return 'ppt';
		} elseif (strpos($this->getMimetype(), 'html')) {
			return 'html';
		} elseif (strpos($this->getMimetype(), 'jpeg')) {
			return 'jpg';
		} elseif (strpos($this->getMimetype(), 'plain')) {
			return 'txt';
		} elseif (strpos($this->getMimetype(), 'png')) {
			return 'png';
		} elseif ($this->getMimetype() == 'application/octet-stream') {
			return 'onbekend';
		} else {
			return $this->getMimetype();
		}
	}

	/**
	 * Centrale plek om het volledige pad van een document te maken.
	 */
	public function getFullPath() {
		return $this->getPath() . $this->getFullFileName();
	}

	/**
	 * @return string location on disk
	 */
	public function getPath() {
		return DATA_PATH . 'documenten/';
	}

	/**
	 * @return string file name on disk
	 */
	public function getFullFileName() {
		return $this->getID() . '_' . $this->filename;
	}

	public function getUrl() {
		return CSR_ROOT . '/communicatie/documenten/bekijken/' . $this->getID() . '/' . $this->getFullFileName();
	}

	public function getDownloadUrl() {
		return CSR_ROOT . '/communicatie/documenten/download/' . $this->getID() . '/' . $this->getFullFileName();
	}

	/**
	 * Bestand opslaan vanuit een string in de juiste map.
	 */
	public function putFile($file) {
		$this->throwExceptionWhenUnsaved();
		$this->throwExceptionWhenDestNotWriteable();

		return file_put_contents($this->getFullPath(), $file);
	}

	/**
	 * Bestand kopieren naar de juiste map.
	 */
	public function copyFile($source) {
		$this->throwExceptionWhenUnsaved();
		$this->throwExceptionWhenDestNotWriteable();

		if (file_exists($source)) {
			return copy($source, $this->getFullPath());
		} else {
			throw new Exception('Bronbestand bestaat niet');
		}
	}

	/**
	 * Bestand opslaan vanuit upload-tempdir.
	 */
	public function moveUploaded($source) {
		$this->throwExceptionWhenUnsaved();
		$this->throwExceptionWhenDestNotWriteable();

		if (is_uploaded_file($source)) {
			return move_uploaded_file($source, $this->getFullPath());
		}
		return false;
	}

	/**
	 * Aangehangen bestand verwijderen van file system.
	 */
	public function deleteFile($throwWhenNotFound = true) {
		if (!$this->hasFile()) {
			if ($throwWhenNotFound) {
				throw new Exception('Geen bestand gevonden voor dit document');
			} else {
				return true;
			}
		}
		if (@unlink($this->getFullPath())) {
			$this->setFileName('');
			return $this->save();
		} else {
			if (is_writable($this->getFullPath())) {
				throw new Exception('Kan bestand niet verwijderen, lijkt wel beschrijfbaar' . $this->getFullPath());
			} else {
				throw new Exception('Kan bestand niet verwijderen, niet beschrijfbaar');
			}
		}
		return false;
	}

	private function throwExceptionWhenUnsaved() {
		if ($this->getID() == 0) {
			throw new Exception('Document moet eerst opgeslagen worden in de database voordat bestand verplaatst kan worden');
		}
	}

	private function throwExceptionWhenDestNotWriteable() {
		if (!is_writable(DATA_PATH . 'documenten')) {
			throw new Exception('Doelmap is niet beschrijfbaar');
		}
	}

}
