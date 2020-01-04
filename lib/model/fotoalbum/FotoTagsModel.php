<?php

namespace CsrDelft\model\fotoalbum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoTag;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;

class FotoTagsModel extends PersistenceModel {

	const ORM = FotoTag::class;
	public function __construct() {
		parent::__static();
		parent::__construct();
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'wanneer DESC';

	public function getTags(Foto $foto) {
		return $this->find('refuuid = ?', array($foto->getUUID()));
	}

	public function addTag(Foto $foto, $uid, $x, $y, $size) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException('Profiel bestaat niet');
		}
		$tag = new FotoTag();
		$tag->refuuid = $foto->getUUID();
		$tag->keyword = $uid;
		$tag->door = LoginModel::getUid();
		$tag->wanneer = getDateTime();
		$tag->x = (int)$x;
		$tag->y = (int)$y;
		$tag->size = (int)$size;
		if ($this->exists($tag)) {
			return $this->retrieve($tag);
		} else {
			parent::create($tag);
			return $tag;
		}
	}

	public function removeTag(
		$refuuid,
		$keyword
	) {
		return $this->deleteByPrimaryKey(array($refuuid, $keyword));
	}

	public function verwijderFotoTags(Foto $foto) {
		foreach ($this->getTags($foto) as $tag) {
			$this->delete($tag);
		}
	}

}
