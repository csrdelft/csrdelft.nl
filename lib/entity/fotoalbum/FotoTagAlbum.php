<?php

namespace CsrDelft\entity\fotoalbum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;

/**
 * FotoTagAlbum.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FotoTagAlbum extends FotoAlbum
{

    /**
     * Lidnummer
     * Foreign key
     * @var string
     */
    public $uid;

    public function __construct($uid)
    {
        parent::__construct();
        // no parent constructor
        $this->uid = $uid;
        $this->subalbums = array();
        $this->path = PHOTOALBUM_PATH;
        $this->dirname = 'Foto\'s met ' . ProfielRepository::getNaam($uid, 'civitas');
    }

    public function modified()
    {
        return time();
    }

    public function getParentName()
    {
        return null;
    }

    public function getUrl()
    {
        return '/fotoalbum/' . $this->uid;
    }

    public function exists()
    {
        return true;
    }

    public function isEmpty()
    {
        return false;
    }

    public function hasFotos($incompleet = false)
    {
        return true;
    }

    /**
     * @param false $incompleet
     * @return Foto[]
     */
    public function getFotos($incompleet = false)
    {
        if (!isset($this->fotos)) {
            // find tagged fotos
            $container = ContainerFacade::getContainer();
            $fotoTagsRepository = $container->get(FotoTagsRepository::class);
            $fotoRepository = $container->get(FotoRepository::class);
            foreach ($fotoTagsRepository->findBy(['keyword' => $this->uid]) as $tag) {
                $foto = $fotoRepository->retrieveByUUID($tag->refuuid);
                if ($foto) {
                    $this->fotos[] = $foto;
                }
            }
        }
        return $this->fotos;
    }

    public function magBekijken()
    {
        return LoginService::mag(P_LEDEN_READ);
    }

    public function isOwner()
    {
        return $this->uid === LoginService::getUid();
    }

}
