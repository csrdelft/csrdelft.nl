<?php


namespace CsrDelft\model\entity;


use CsrDelft\common\CsrException;
use CsrDelft\model\ForumPlaatjeModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\formulier\uploadvelden\ImageField;
use CsrDelft\view\formulier\uploadvelden\UploadFileField;

class ForumPlaatje extends PersistentEntity
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $access_key;

	public $datum_toegevoegd;

	public $maker;

	public $source_url;
	/**
	 * Plaatje constructor.
	 * @param string $id
	 */
	public function __construct() {
	}

	public function getAfbeelding() {
		return new Afbeelding(PLAATJES_PATH . strval($this->id));
	}

	public function exists() {
		return $this->getAfbeelding()->exists();
	}

	public function getUrl() {
		return "/plaatjes/bekijken/$this->access_key";
	}

	/**
	 * @var string
	 */
	protected static $table_name = 'forumplaatjes';

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'access_key' => [T::StringKey, false],
		'maker' => [T::UID, true],
		'datum_toegevoegd' => [T::DateTime, false],
		'source_url' => [T::Text, true],
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}
