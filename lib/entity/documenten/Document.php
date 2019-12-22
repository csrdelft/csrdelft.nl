<?php

namespace CsrDelft\entity\documenten;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\Bestand;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\Icon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Table("Document")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\documenten\DocumentRepository")
 */
class Document extends Bestand {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $naam;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $categorie_id;
	/**
	 * @var DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $toegevoegd;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $eigenaar;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $leesrechten = P_LOGGED_IN;

	/**
	 * Bestandsnaam
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $filename;
	/**
	 * Bestandsgrootte in bytes
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $filesize;
	/**
	 * Mime-type van het bestand
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $mimetype;
	/**
	 * Locatie van bestand
	 * @var string
	 */
	public $directory;

	/**
	 * Bestaat er een bestand met de naam in de map.
	 *
	 * @return bool
	 */
	public function exists() {
		return @is_readable($this->directory . '/' . $this->filename) AND is_file($this->directory . '/' . $this->filename);
	}

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
		return LoginModel::mag($this->leesrechten) && LoginModel::mag(P_LOGGED_IN);
	}

	public function magBewerken() {
		return $this->isEigenaar() OR LoginModel::mag(P_DOCS_MOD);
	}

	public function magVerwijderen() {
		return LoginModel::mag(P_DOCS_MOD);
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
}
