<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\fotoalbum\FotoTagsModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\fotoalbum\FotoAlbumToevoegenForm;
use CsrDelft\view\fotoalbum\FotosDropzone;
use CsrDelft\view\fotoalbum\FotoTagToevoegenForm;
use CsrDelft\view\fotoalbum\PosterUploadForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * FotoAlbumController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het fotoalbum.
 */
class FotoAlbumController {
	use QueryParamTrait;

	private $model;

	public function __construct() {
		$this->model = FotoAlbumModel::instance();
	}

	public function bekijken($dir) {
		if ($dir == "" && !LoginModel::mag(P_ALBUM_READ)) {
			$dir = 'Publiek';
		}

		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magBekijken()) {
			throw new CsrToegangException();
		}

		if ($album->dirname === 'Posters') {
			$album->orderByDateModified();
		}
		return view('fotoalbum.album', ['album' => $album]);
	}

	public function verwerken($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		if ($album->dirname === 'fotoalbum') {
			setMelding('Niet het complete fotoalbum verwerken', -1);
			redirect($album->getUrl());
		}
		$this->model->verwerkFotos($album);
		redirect($album->getUrl());
	}

	public function toevoegen($dir) {
		$album = new FotoAlbum($dir);
		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($this->getMethod() == 'POST' AND $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path = join_paths($album->path, $subalbum);
			$album->subdir = join_paths($album->subdir, $subalbum);
			if (!$album->exists()) {
				$this->model->create($album);
			}
			return new JsonResponse($album->getUrl());
		}
		return $formulier;
	}

	public function uploaden($dir) {
		$album = $this->model->getFotoAlbum($dir);

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
		if ($this->getMethod() == 'POST') {
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
					if ($foto->exists()) {
						FotoModel::instance()->verwerkFoto($foto);
						// verwerken gelukt?
						if ($foto->isComplete()) {
							if ($poster) {
								redirect($album->getUrl() . '#' . $foto->getResizedUrl());
							} else {
								return new JsonResponse(true);
							}
						} else {
							throw new CsrGebruikerException('Verwerken mislukt');
						}
					} else {
						throw new CsrGebruikerException('Opslaan mislukt');
					}
				} catch (CsrGebruikerException $e) {
					return new JsonResponse(array('error' => $e->getMessage()), 500);
				}
			} else {
				if ($poster) {
					// fall through
				} elseif ($uploader !== null) {
					return new JsonResponse(array('error' => $uploader->getError()), 500);
				}
			}
		}
		return view('default', ['content' => $formulier]);
	}

	public function bestaande($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$list = [];
		$files = $album->getFotos();
		if ($files !== false) {
			foreach ($files as $filename) {
				$afbeelding = new Afbeelding($filename->getThumbPath());
				if (endsWith($afbeelding->filename, '.jpg')) {
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
		$album = $this->model->getFotoAlbum($dir);

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

	public function hernoemen($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$naam = trim(filter_input(INPUT_POST, 'Nieuwe_naam', FILTER_SANITIZE_STRING));
		if ($album !== null) {
			try {
				$this->model->hernoemAlbum($album, $naam);
			} catch (CsrException $exception) {
				return new JsonResponse($exception->getMessage(), 400);
			}
			return new JsonResponse($album->getUrl());
		} else {
			return new JsonResponse('Fotoalbum hernoemen mislukt', 500);
		}
	}

	public function albumcover($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$cover = new Foto($filename, $album);
		if ($cover->exists() AND $this->model->setAlbumCover($album, $cover)) {
			return new JsonResponse($album->getUrl() . '#' . $cover->getResizedUrl());
		} else {
			return new JsonResponse('Fotoalbum-cover instellen mislukt', 500);
		}
	}

	public function verwijderen($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magVerwijderen()) {
			throw new CsrToegangException();
		}
		if ($album->isEmpty()) {
			if (1 === FotoAlbumModel::instance()->delete($album)) {
				setMelding('Fotoalbum verwijderen geslaagd', 1);
				return new JsonResponse(dirname($album->getUrl()));
			} else {
				setMelding('Fotoalbum verwijderen mislukt', -1);
				return new JsonResponse($album->getUrl());
			}
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (FotoModel::instance()->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit;
		} else {
			return new JsonResponse('Foto verwijderen mislukt', 500);
		}
	}

	public function roteren($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magAanpassen()) {
			throw new CsrToegangException();
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		$degrees = (int)filter_input(INPUT_POST, 'rotation', FILTER_SANITIZE_NUMBER_INT);
		$foto->rotate($degrees);
		return new JsonResponse(true);
	}

	public function zoeken($zoekterm = null) {
		if (!$zoekterm && !$this->hasParam('q')) {
			throw new CsrToegangException();
		}

		if (!$zoekterm) {
			$zoekterm = $this->getParam('q');
		}
		$query = iconv('utf-8', 'ascii//TRANSLIT', $zoekterm); // convert accented characters to regular
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('subdir LIKE ?', array('%'. $query . '%'), null, 'subdir DESC', $limit) as $album) {
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

	public function gettags($dir) {
		$album = $this->model->getFotoAlbum($dir);

		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw new CsrToegangException();
		}
		// return all tags
		$tags = FotoTagsModel::instance()->getTags($foto);
		return new JsonResponse($tags->fetchAll());
	}

	public function addtag($dir) {
		$album = $this->model->getFotoAlbum($dir);

		if (!$album->magToevoegen()) {
			throw new CsrToegangException();
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw new CsrToegangException();
		}
		$formulier = new FotoTagToevoegenForm($foto);
		if ($this->getMethod() == 'POST' AND $formulier->validate()) {
			$uid = $formulier->findByName('uid')->getValue();
			$x = $formulier->findByName('x')->getValue();
			$y = $formulier->findByName('y')->getValue();
			$size = $formulier->findByName('size')->getValue();
			FotoTagsModel::instance()->addTag($foto, $uid, $x, $y, $size);
			// return all tags
			$tags = FotoTagsModel::instance()->getTags($foto);
			return new JsonResponse($tags->fetchAll());
		} else {
			return $formulier;
		}
	}

	public function removetag() {
		$refuuid = filter_input(INPUT_POST, 'refuuid', FILTER_SANITIZE_STRING);
		$keyword = filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING);
		if (!LoginModel::mag(P_ALBUM_MOD) AND !LoginModel::mag($keyword)) {
			throw new CsrToegangException();
		}
		FotoTagsModel::instance()->removeTag($refuuid, $keyword);
		/** @var Foto $foto */
		$foto = FotoModel::instance()->retrieveByUUID($refuuid);
		if ($foto) {
			// return all tags
			$tags = FotoTagsModel::instance()->getTags($foto);
			return new JsonResponse($tags->fetchAll());
		} else {
			return new JsonResponse(array());
		}
	}

	public function raw_image($dir, $foto, $ext) {
		//Extra check to prevent attacks
		if (!path_valid(PHOTOALBUM_PATH, join_paths($dir, $foto . "." . $ext))) {
			throw new CsrToegangException();
		}

		$image = new Foto($foto . '.' . $ext, new FotoAlbum($dir), true);
		if ($image === false || !$image->magBekijken()) {
			throw new CsrToegangException();
		} else if (!$image->exists()) {
			throw new CsrToegangException();
		}
		$response = new BinaryFileResponse($image->getFullPath());
		if (isset($_GET['download'])) {
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $image->filename);
		} else {
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $image->filename);
		}

		return $response;
	}

	public function raw_image_thumb($dir, $foto, $ext) {
		if (!path_valid(PHOTOALBUM_PATH, join_paths($dir, $foto . "." . $ext))) {
			throw new NotFoundHttpException();
		}
		$foto = new Foto($foto . "." . $ext, new FotoAlbum($dir));
		$afbeelding = new Afbeelding($foto->getThumbPath());

		return new BinaryFileResponse($afbeelding->getFullPath());
	}

	public function raw_image_resized($dir, $foto, $ext) {
		if (!path_valid(PHOTOALBUM_PATH, join_paths($dir, $foto . "." . $ext))) {
			throw new NotFoundHttpException();
		}
		$foto = new Foto($foto . "." . $ext, new FotoAlbum($dir));
		$afbeelding = new Afbeelding($foto->getResizedPath());
		return new BinaryFileResponse($afbeelding->getFullPath());
	}
}
