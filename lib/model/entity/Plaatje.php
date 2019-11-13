<?php


namespace CsrDelft\model\entity;


use CsrDelft\common\CsrException;
use CsrDelft\view\formulier\uploadvelden\ImageField;
use CsrDelft\view\formulier\uploadvelden\UploadFileField;

class Plaatje
{

	public $uid;
	/**
	 * Plaatje constructor.
	 */
	public function __construct($uid)
	{
		if (!static::isValidId($uid)) {
			throw new CsrException("Wrong uid for plaatje: ".$uid);
		}
		$this->uid = $uid;
	}

	public static function create(ImageField $uploader)
	{
		$plaatje = new Plaatje(bin2hex(random_bytes(16)));
		$uploader->opslaan(PLAATJES_PATH, $plaatje->uid);
		return $plaatje;
	}

	public static function isValidId($id)
	{
		return preg_match('/^[a-zA-Z0-9]{32}$/', $id);
	}

	public function getAfbeelding()
	{
		return new Afbeelding(PLAATJES_PATH . $this->uid);
	}

	public function exists() {
		return $this->getAfbeelding()->exists();
	}

	public function getUrl()
	{
		return "/plaatjes/bekijken/$this->uid";
	}
}
