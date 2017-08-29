<?php

namespace CsrDelft\model\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

class FotoModel extends PersistenceModel
{

    const ORM = Foto::class;
    const DIR = 'fotoalbum/';

    protected static $instance;

    /**
     * @override parent::retrieveByUUID($UUID)
     */
    public function retrieveByUUID($UUID)
    {
        $parts = explode('@', $UUID, 2);
        $path = explode('/', $parts[0]);
        $filename = array_pop($path);
        $subdir = implode('/', $path) . '/';
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

    public function create(PersistentEntity $foto)
    {
        $foto->owner = LoginModel::getUid();
        $foto->rotation = 0;
        parent::create($foto);
    }

    public function verwerkFoto(Foto $foto)
    {
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

    public function verwijderFoto(Foto $foto)
    {
        $ret = true;
        $ret &= unlink($foto->directory . $foto->filename);
        if ($foto->hasResized()) {
            $ret &= unlink($foto->getResizedPath());
        }
        if ($foto->hasThumb()) {
            $ret &= unlink($foto->getThumbPath());
        }
        if ($ret) {
            $this->delete($foto);
            \CsrDelft\model\fotoalbum\FotoTagsModel::instance()->verwijderFotoTags($foto);
        }
        return $ret;
    }

}
