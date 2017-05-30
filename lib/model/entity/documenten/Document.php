<?php

namespace CsrDelft\model\entity\documenten;

use CsrDelft\model\entity\Bestand;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use Exception;

/**
 * Class Document.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class Document extends Bestand {
	public $id;
	public $naam;
	public $categorie_id;
	public $toegevoegd;
	public $eigenaar;
	public $leesrechten = 'P_LOGGED_IN';

	public function hasFile() {
		if (!$this->magBekijken()) {
			return false;
		}
		return $this->filename != '' AND file_exists($this->getFullPath());
	}

	public function isEigenaar() {
		return LoginModel::getUid() === $this->eigenaar;
	}

	public function magBekijken() {
		return LoginModel::mag($this->leesrechten);
	}

	public function magBewerken() {
		return $this->isEigenaar() OR LoginModel::mag('P_DOCS_MOD');
	}

	public function magVerwijderen() {
		return LoginModel::mag('P_DOCS_MOD');
	}

	/**
	 * @return string file name on disk
	 */
	public function getFullFileName() {
		return $this->id . '_' . $this->filename;
	}

	/**
	 * @return string location on disk
	 */
	public function getPath() {
		return DATA_PATH . 'documenten/';
	}

	public function getFullPath() {
		return $this->getPath() . $this->getFullFileName();
	}

	public function getUrl() {
		return '/documenten/bekijken/' . $this->id . '/' . rawurlencode($this->filename);
	}

	public function getDownloadUrl() {
		return '/documenten/download/' . $this->id . '/' . rawurlencode($this->filename);
	}

	public function getFriendlyMimetype() {
		if (strpos($this->mimetype, 'pdf')) {
			return 'pdf';
		} elseif (strpos($this->mimetype, 'msword') OR strpos($this->mimetype, 'officedocument.word')) {
			return 'doc';
		} elseif (strpos($this->mimetype, 'officedocument.pres')) {
			return 'ppt';
		} elseif (strpos($this->mimetype, 'html')) {
			return 'html';
		} elseif (strpos($this->mimetype, 'jpeg')) {
			return 'jpg';
		} elseif (strpos($this->mimetype, 'plain')) {
			return 'txt';
		} elseif (strpos($this->mimetype, 'png')) {
			return 'png';
		} elseif ($this->mimetype == 'application/octet-stream') {
			return 'onbekend';
		} else {
			return $this->mimetype;
		}
	}

	/**
	 * Aangehangen bestand verwijderen van file system.
	 * @param bool $throwWhenNotFound
	 * @return bool
	 * @throws Exception
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
			$this->filename = '';
			return true;
		} else {
			if (is_writable($this->getFullPath())) {
				throw new Exception('Kan bestand niet verwijderen, lijkt wel beschrijfbaar' . $this->getFullPath());
			} else {
				throw new Exception('Kan bestand niet verwijderen, niet beschrijfbaar');
			}
		}
	}

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'naam' => [T::String],
		'categorie_id' => [T::Integer],
		'filename' => [T::String],
		'filesize' => [T::Integer],
		'mimetype' => [T::String],
		'toegevoegd' => [T::DateTime],
		'eigenaar' => [T::UID],
		'leesrechten' => [T::String],
	];
	protected static $table_name = 'Document';
	protected static $primary_key = ['id'];
}
