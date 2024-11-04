<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Security\Voter\Entity\FotoAlbumVoter;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\view\fotoalbum\FotoAlbumBreadcrumbs;
use CsrDelft\view\fotoalbum\FotoAlbumToevoegenForm;
use CsrDelft\view\fotoalbum\FotosDropzone;
use CsrDelft\view\fotoalbum\FotoTagToevoegenForm;
use CsrDelft\view\fotoalbum\PosterUploadForm;
use CsrDelft\view\Icon;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FotoAlbumController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het fotoalbum.
 */
class FotoAlbumController extends AbstractController
{


	/**
	 * @return void
	 */
	private function assertValidFotoPath(string $dir, string $foto)
	{
		if (!preg_match('/\.(JPE?G|PNG|jpe?g|png)/', (string) $foto)) {
			throw $this->createNotFoundException();
		}
		if (
			!PathUtil::path_valid(PHOTOALBUM_PATH, PathUtil::join_paths($dir, $foto))
		) {
			throw $this->createNotFoundException();
		}
	}
}
