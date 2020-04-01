<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\view\fotoalbum\FotoAlbumToevoegenForm;
use CsrDelft\view\fotoalbum\FotosDropzone;
use CsrDelft\view\fotoalbum\FotoTagToevoegenForm;
use CsrDelft\view\fotoalbum\PosterUploadForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FotoAlbumController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het fotoalbum.
 */
class FotoAlbumController extends AbstractController {
	private $fotoAlbumRepository;
	/**
	 * @var FotoTagsRepository
	 */
	private $fotoTagsRepository;
	/**
	 * @var FotoRepository
	 */
	private $fotoRepository;

	public function __construct(
		FotoTagsRepository $fotoTagsRepository,
		FotoAlbumRepository $fotoAlbumRepository,
		FotoRepository $fotoRepository
	) {
		$this->fotoTagsRepository = $fotoTagsRepository;
		$this->fotoAlbumRepository = $fotoAlbumRepository;
		$this->fotoRepository = $fotoRepository;
	}

	public function bekijken($dir) {
		if ($dir == "" && !LoginModel::mag(P_ALBUM_READ)) {
			$dir = 'Publiek';
		}

		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magBekijken()) {
			throw new CsrToegangException();
		}

		if ($album->dirname === 'Posters') {
			$album->orderByDateModified();
		}
		return view('fotoalbum.album', ['album' => $album]);
	}

	public function verwerken($dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		if ($album->dirname === 'fotoalbum') {
			setMelding('Niet het complete fotoalbum verwerken', -1);
			return $this->csrRedirect($album->getUrl());
		}
		$this->fotoAlbumRepository->verwerkFotos($album);
		return $this->csrRedirect($album->getUrl());
	}

	public function toevoegen(Request $request, $dir) {
		$album = new FotoAlbum($dir);
		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($request->getMethod() == 'POST' && $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path = join_paths($album->path, $subalbum);
			$album->subdir = join_paths($album->subdir, $subalbum);
			if (!$album->exists()) {
				$this->fotoAlbumRepository->create($album);
			}
			return new JsonResponse($album->getUrl());
		}
		return $formulier;
	}

	public function uploaden(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$poster = $album->dirname === 'Posters';
		if ($poster) {
			$formulier = new PosterUploadForm($album);
			$uploader = $formulier->findByName('afbeelding');
		} else {
			$formulier = new FotosDropzone($album);
			$uploader = $formulier->getPostedUploader();
		}
		if ($request->getMethod() == 'POST') {
			if ($formulier->validate()) {
				try {
					if ($poster) {
						$filename = $formulier->findByName('posternaam')->getValue() . '.jpg';
						if (strpos($filename, 'folder') !== false) {
							throw new CsrGebruikerException('Albumcover niet toegestaan');
						}
					} else {
						$filename = $uploader->getModel()->filename;
					}
					$uploader->opslaan($album->path, $filename);
					$foto = new Foto($filename, $album);
					// opslaan gelukt?
					if (!$foto->exists()) {
						throw new CsrGebruikerException('Opslaan mislukt');
					}
					$this->fotoRepository->verwerkFoto($foto);
					// verwerken gelukt?
					if (!$foto->isComplete()) {
						throw new CsrGebruikerException('Verwerken mislukt');
					}

					if ($poster) {
						return $this->csrRedirect($album->getUrl() . '#' . $foto->getResizedUrl());
					} else {
						return new JsonResponse(true);
					}
				} catch (CsrGebruikerException $e) {
					return new JsonResponse(array('error' => $e->getMessage()), 500);
				}
			} else {
				if (!$poster && $uploader !== null) {
					return new JsonResponse(array('error' => $uploader->getError()), 500);
				}
			}
		}
		return view('default', ['content' => $formulier]);
	}

	public function bestaande($dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$list = [];
		$files = $album->getFotos();
		if ($files !== false) {
			foreach ($files as $filename) {
				$afbeelding = new Afbeelding($filename->getThumbPath());
				if (endsWith($afbeelding->filename, '.jpg')) {
					$obj = [];
					$obj['name'] = $afbeelding->filename;
					$obj['size'] = $afbeelding->filesize;
					$obj['type'] = $afbeelding->mimetype;
					$obj['thumbnail'] = $filename->getThumbUrl();
					$list[] = $obj;
				}
			}
		}
		return new JsonResponse($list);
	}

	public function downloaden($dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magDownloaden()) {
			throw new CsrToegangException();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-tar');
		header('Content-Disposition: attachment; filename="' . $album->dirname . '.tar"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		$fotos = $album->getFotos();
		set_time_limit(0);
		$cmd = "tar cC " . escapeshellarg($album->path);
		foreach ($fotos as $foto) {
			$cmd .= ' ' . escapeshellarg($foto->filename);
		}
		$fh = popen($cmd, 'r');
		while (!feof($fh)) {
			print fread($fh, 8192);
		}
		pclose($fh);
		exit;
	}

	public function hernoemen(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$naam = trim($request->request->get('naam'));
		if ($album !== null) {
			try {
				$this->fotoAlbumRepository->hernoemAlbum($album, $naam);
			} catch (CsrException $exception) {
				return new JsonResponse($exception->getMessage(), 400);
			}
			return new JsonResponse($album->getUrl());
		} else {
			return new JsonResponse('Fotoalbum hernoemen mislukt', 500);
		}
	}

	public function albumcover(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$filename = $request->request->get('foto');
		$cover = new Foto($filename, $album);
		if ($cover->exists() && $this->fotoAlbumRepository->setAlbumCover($album, $cover)) {
			return new JsonResponse($album->getUrl() . '#' . $cover->getResizedUrl());
		} else {
			return new JsonResponse('Fotoalbum-cover instellen mislukt', 500);
		}
	}

	public function verwijderen(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magVerwijderen()) {
			throw new CsrToegangException();
		}
		if ($album->isEmpty()) {
			try {
				$this->fotoAlbumRepository->delete($album);
				setMelding('Fotoalbum verwijderen geslaagd', 1);
				return new JsonResponse(dirname($album->getUrl()));
			} catch (ORMException $ex) {
				setMelding('Fotoalbum verwijderen mislukt', -1);
				return new JsonResponse($album->getUrl());
			}
		}
		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		if ($this->fotoRepository->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit;
		} else {
			return new JsonResponse('Foto verwijderen mislukt', 500);
		}
	}

	public function roteren(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		$degrees = $request->request->getInt('rotation');
		$this->fotoRepository->rotate($foto, $degrees);
		return new JsonResponse(true);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}

		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$query = iconv('utf-8', 'ascii//TRANSLIT', $zoekterm); // convert accented characters to regular
		$limit = $request->query->getInt('limit', 5);
		$result = array();
		foreach ($this->fotoAlbumRepository->zoeken($query, $limit) as $album) {
			/** @var FotoAlbum $album */
			$result[] = array(
				'icon' => Icon::getTag('fotoalbum', null, 'Fotoalbum', 'mr-2'),
				'url' => $album->getUrl(),
				'label' => $album->getParentName(),
				'value' => ucfirst($album->dirname)
			);
		}
		return new JsonResponse($result);
	}

	public function gettags(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw new CsrToegangException();
		}
		// return all tags
		$tags = $this->fotoTagsRepository->getTags($foto);
		return new JsonResponse($tags);
	}

	public function addtag(Request $request, $dir) {
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw new CsrToegangException();
		}
		$formulier = new FotoTagToevoegenForm($foto);
		if ($request->getMethod() == 'POST' && $formulier->validate()) {
			$uid = $formulier->findByName('uid')->getValue();
			$x = $formulier->findByName('x')->getValue();
			$y = $formulier->findByName('y')->getValue();
			$size = $formulier->findByName('size')->getValue();
			$this->fotoTagsRepository->addTag($foto, $uid, $x, $y, $size);
			// return all tags
			$tags = $this->fotoTagsRepository->getTags($foto);
			return new JsonResponse($tags);
		} else {
			return $formulier;
		}
	}

	public function removetag(Request $request) {
		$refuuid = $request->request->get('refuuid');
		$keyword = $request->request->get('keyword');
		if (!LoginModel::mag(P_ALBUM_MOD) && !LoginModel::mag($keyword)) {
			throw new CsrToegangException();
		}
		$this->fotoTagsRepository->removeTag($refuuid, $keyword);
		/** @var Foto $foto */
		$foto = $this->fotoRepository->retrieveByUUID($refuuid);
		if ($foto) {
			// return all tags
			$tags = $this->fotoTagsRepository->getTags($foto);
			return new JsonResponse($tags);
		} else {
			return new JsonResponse(array());
		}
	}

	public function raw_image(Request $request, $type, $dir, $foto, $ext) {
		//Extra check to prevent attacks
		if (!path_valid(PHOTOALBUM_PATH, join_paths($dir, $foto . "." . $ext))) {
			throw new CsrToegangException();
		}

		$image = new Foto($foto . '.' . $ext, new FotoAlbum($dir), true);

		if (!$image->magBekijken()) {
			throw new CsrToegangException();
		} else if (!$image->exists()) {
			throw new CsrToegangException();
		}

		if ($type == 'full') {
			$path = $image->getFullPath();
		} elseif ($type == 'thumb') {
			$path = $image->getThumbPath();
		} elseif ($type == 'resized') {
			$path = $image->getResizedPath();
		} else {
			throw new CsrException("raw_image type: " . $type . " wordt niet afgehandeld");
		}

		$response = new BinaryFileResponse($path, 200, [], true, null, true);
		$response->setContentDisposition($request->query->has('download') ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE, $image->filename);
		$response->setExpires(date_create('+1 day'));
		$response->isNotModified($request);

		return $response;
	}
}
