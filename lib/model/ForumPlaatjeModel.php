<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\ForumPlaatje;
use CsrDelft\model\entity\GeoLocation;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\view\formulier\uploadvelden\ImageField;


class ForumPlaatjeModel extends PersistenceModel
{

	const ORM = ForumPlaatje::class;
	public static function generate()
	{
		$plaatje = new ForumPlaatje();
		$plaatje->datum_toegevoegd = getDateTime();
		$plaatje->access_key = bin2hex(random_bytes(16));
		return $plaatje;
	}

	public static function fromUploader(ImageField $uploader, $uid) {
		$plaatje = static::generate();
		$plaatje->maker = $uid;
		$plaatje->id = static::instance()->create($plaatje);
		$uploader->opslaan(PLAATJES_PATH, strval($plaatje->id));
		$plaatje->createResized();
		return $plaatje;
	}

	public static function isValidKey($key) {
		return preg_match('/^[a-zA-Z0-9]{32}$/', $key);
	}

	/**
	 * @param $key
	 * @return ForumPlaatje|false
	 */
	public static function getByKey($key) {
		return static::instance()->find("access_key = ?", [$key])->fetch();
	}

}
