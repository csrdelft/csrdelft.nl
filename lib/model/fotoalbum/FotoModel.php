<?php

namespace CsrDelft\model\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

class FotoModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = Foto::class;

	/**
	 * @var FotoTagsModel
	 */
	private $fotoTagsModel;

	public function __construct(FotoTagsModel $fotoTagsModel) {
		parent::__construct();

		$this->fotoTagsModel = $fotoTagsModel;
	}

	/**
	 * @override parent::retrieveByUUID($UUID)
	 */
	public function retrieveByUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$path = explode('/', $parts[0]);
		$filename = array_pop($path);
		$subdir = implode('/', $path);
		return $this->retrieveByPrimaryKey(array($subdir, $filename));
	}

	/**
	 * Create database entry if foto does not exist.
	 *
	 * @param Foto|PersistentEntity $foto
	 * @param array $attributes
	 *
	 * @return mixed false on failure
	 */
	public function retrieveAttributes(
		PersistentEntity $foto,
		array $attributes
	) {
		$this->verwerkFoto($foto);
		return parent::retrieveAttributes($foto, $attributes);
	}

	/**
	 * @param PersistentEntity|Foto $foto
	 * @return string
	 */
	public function create(PersistentEntity $foto) {
		$foto->owner = LoginModel::getUid();
		$foto->rotation = 0;
		return parent::create($foto);
	}

	/**
	 * @param Foto $foto
	 * @throws CsrException
	 */
	public function verwerkFoto(Foto $foto) {
		if (!$this->exists($foto)) {
			$this->create($foto);
			if (false === @chmod($foto->getFullPath(), 0644)) {
				throw new CsrException('Geen eigenaar van foto: ' . htmlspecialchars($foto->getFullPath()));
			}
		}
		if (!$foto->hasThumb()) {
			$foto->createThumb();
		}
		if (!$foto->hasResized()) {
			$foto->createResized();
		}
	}

	/**
	 * @param Foto $foto
	 * @return bool
	 */
	public function verwijderFoto(Foto $foto) {
		$ret = true;
		$ret &= unlink($foto->getFullPath());
		if ($foto->hasResized()) {
			$ret &= unlink($foto->getResizedPath());
		}
		if ($foto->hasThumb()) {
			$ret &= unlink($foto->getThumbPath());
		}
		if ($ret) {
			$this->delete($foto);
			$this->fotoTagsModel->verwijderFotoTags($foto);
		}
		return $ret;
	}

}
