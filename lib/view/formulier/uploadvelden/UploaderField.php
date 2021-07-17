<?php


namespace CsrDelft\view\formulier\uploadvelden;


use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\view\formulier\invoervelden\TextField;

/**
 * Abstracte klasse om het opslaan van bestanden te overzien.
 */
abstract class UploaderField extends TextField
{
	public function __construct($name, $value, $description, $model = null)
	{
		parent::__construct($name, $value, $description, null, null, $model);
	}

	abstract public function isAvailable();

	/**
	 * Bestand opslaan op de juiste plek.
	 *
	 * @param string $directory fully qualified path with trailing slash
	 * @param string $filename filename with extension
	 * @param boolean $overwrite allowed to overwrite existing file
	 * @throws CsrException Ongeldige bestandsnaam, doelmap niet schrijfbaar of naam ingebruik
	 */
	public function opslaan($directory, $filename, $overwrite = false) {
		if (!$this->isAvailable()) {
			throw new CsrException('Uploadmethode niet beschikbaar: ' . get_class($this));
		}
		if (!$this->validate()) {
			throw new CsrGebruikerException($this->getError());
		}
		if (!valid_filename($filename)) {
			throw new CsrGebruikerException('Ongeldige bestandsnaam: ' . htmlspecialchars($filename));
		}
		if (!file_exists($directory)) {
			mkdir($directory);
		}
		if (false === @chmod($directory, 0755)) {
			throw new CsrException('Geen eigenaar van map: ' . htmlspecialchars($directory));
		}
		if (!is_writable($directory)) {
			throw new CsrException('Doelmap is niet beschrijfbaar: ' . htmlspecialchars($directory));
		}
		if (file_exists(join_paths($directory, $filename))) {
			if ($overwrite) {
				if (!unlink(join_paths($directory, $filename))) {
					throw new CsrException('Overschrijven mislukt: ' . htmlspecialchars(join_paths($directory, $filename)));
				}
			} elseif (!$this instanceof BestandBehouden) {
				throw new CsrGebruikerException('Bestandsnaam al in gebruik: ' . htmlspecialchars(join_paths($directory, $filename)));
			}
		}
	}

}
