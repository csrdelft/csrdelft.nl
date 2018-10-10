<?php

namespace CsrDelft\model\entity\documenten;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\Bestand;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\Icon;

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

	public function getMimetypeIcon() {
		return Icon::getTag('mime-' . $this->getFriendlyMimetype());
	}

	public function getFriendlyMimetype() {
		$mimetypeMap = [
			'application/pdf' => 'pdf',
			'application/zip' => 'zip',
			'application/msword' => 'word',
			'application/vnd.ms-excel' => 'excel',
			'audio/mp3' => 'audio',
			'audio/x-wav' => 'audio',
			'text/rtf' => 'word',
			'application/vnd.ms-powerpoint' => 'powerpoint',
			'text/plain' => 'plain',
			'image/jpeg' => 'image',
			'image/png' => 'image',
			'application/x-rar' => 'zip',
			'application/rar' => 'zip',
			'image/x-wmf' => 'image',
			'application/force-download' => 'onbekend',
			'application/x-pdf' => 'pdf',
			'image/bmp' => 'image',
			'application/rtf' => 'word',
			'audio/mpeg' => 'audio',
			'application/rar-x' => 'zip',
			'application/vnd.openxmlformats-officedocument.spre' => 'excel',
			'application/vnd.openxmlformats-officedocument.word' => 'word',
			'application/vnd.openxmlformats-officedocument.pres' => 'powerpoint',
			'application/x-zip-compressed' => 'zip',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'powerpoint',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
			'application/octet-stream' => 'onbekend',
			'text/pdf' => 'pdf',
			'application/vnd.ms-excel.sheet.macroenabled.12' => 'excel',
		];

		if (key_exists($this->mimetype, $mimetypeMap)) {
			return $mimetypeMap[$this->mimetype];
		} else {
			return 'onbekend';
		}
	}

	/**
	 * Aangehangen bestand verwijderen van file system.
	 *
	 * @param bool $throwWhenNotFound
	 *
	 * @return bool
	 * @throws CsrException
	 */
	public function deleteFile($throwWhenNotFound = true) {
		if (!$this->hasFile()) {
			if ($throwWhenNotFound) {
				throw new CsrGebruikerException('Geen bestand gevonden voor dit document');
			} else {
				return true;
			}
		}
		if (@unlink($this->getFullPath())) {
			$this->filename = '';
			return true;
		} else {
			if (is_writable($this->getFullPath())) {
				throw new CsrException('Kan bestand niet verwijderen, lijkt wel beschrijfbaar' . $this->getFullPath());
			} else {
				throw new CsrException('Kan bestand niet verwijderen, niet beschrijfbaar');
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
