<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\fotoalbum\FotoTagsModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutOweePage;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fotoalbum\FotoAlbumToevoegenForm;
use CsrDelft\view\fotoalbum\FotoAlbumView;
use CsrDelft\view\fotoalbum\FotosDropzone;
use CsrDelft\view\fotoalbum\FotoTagToevoegenForm;
use CsrDelft\view\fotoalbum\PosterUploadForm;
use CsrDelft\view\JsonResponse;

/**
 * FotoAlbumController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het fotoalbum.
 *
 * @property FotoAlbumModel $model
 */
class FotoAlbumController extends AclController {

	public function __construct($query) {
		parent::__construct($query, FotoAlbumModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'bekijken' => 'P_ALBUM_READ|P_ALBUM_PUBLIC_READ',
				'download' => 'P_ALBUM_READ|P_ALBUM_PUBLIC_READ',
				'downloaden' => 'P_ALBUM_DOWN|P_ALBUM_PUBLIC_READ',
				'verwerken' => 'P_ALBUM_MOD|P_ALBUM_PUBLIC_MOD',
				'uploaden' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'zoeken' => 'P_LEDEN_READ',
				'raw_image' => 'P_ALBUM_READ|P_ALBUM_PUBLIC_READ'
			);
		} else {
			$this->acl = array(
				'albumcover' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'verwijderen' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'hernoemen' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'roteren' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'toevoegen' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'bestaande' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'uploaden' => 'P_ALBUM_ADD|P_ALBUM_PUBLIC_ADD',
				'gettags' => 'P_LEDEN_READ',
				'addtag' => 'P_LEDEN_READ',
				'removetag' => 'P_LEDEN_READ'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->getParam(1) == 'plaetjes') {
			$this->action = 'raw_image';
			$path = $this->getParams(2);

		} elseif (!array_key_exists($this->action, $this->acl)) {
			$this->action = 'bekijken';
			$path = $this->getParams(1);
		} elseif ($this->action === 'removetag') {
			parent::performAction();
			return;
		} elseif ($this->action === 'zoeken') {
			parent::performAction($this->getParams(3));
			return;
		} else {
			$path = $this->getParams(3);
		}
		$album = null;
		if (sizeof($path) === 2) {
			$album = $this->model->getFotoAlbum(end($path));
		}
		if (!$album) {
			$path = PHOTOALBUM_PATH . urldecode(implode('/', $path));
			if ($this->action === 'download' or $this->action === 'raw_image') {
				parent::performAction(array($path));
				return;
			}
			$album = $this->model->getFotoAlbum($path);
		}
		if (!$album) {
			setMelding('Fotoalbum bestaat niet' . (DEBUG ? ': ' . $path : ''), -1);
			if (LoginModel::mag('P_ALBUM_READ')) {
				redirect('/fotoalbum');
			} else {
				redirect('/fotoalbum/Publiek');
			}
		}
		$args[] = $album;
		parent::performAction($args);
	}

	public function bekijken(FotoAlbum $album) {
		if (!$album->magBekijken()) {
			$this->exit_http(403);
		}
		if ($album->dirname === 'Posters') {
			$album->orderByDateModified();
		}
		$body = new FotoAlbumView($album);
		// uitgelogd heeft nieuwe layout
		if (LoginModel::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			$this->view = new CsrLayoutOweePage($body);
		}
		$this->view->addCompressedResources('fotoalbum');
	}

	public function verwerken(FotoAlbum $album) {
		if (!$album->magAanpassen()) {
			$this->exit_http(403);
		}
		if ($album->dirname === 'fotoalbum') {
			setMelding('Niet het complete fotoalbum verwerken', -1);
			redirect($album->getUrl());
		}
		$this->model->verwerkFotos($album);
		redirect($album->getUrl());
	}

	public function toevoegen(FotoAlbum $album) {
		if (!$album->magToevoegen()) {
			$this->exit_http(403);
		}
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($this->getMethod() == 'POST' AND $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path .= $subalbum . '/';
			$album->subdir .= $subalbum . '/';
			if (!$album->exists()) {
				$this->model->create($album);
			}
			$this->view = new JsonResponse($album->getUrl());
			return;
		}
		$this->view = $formulier;
	}

	public function uploaden(FotoAlbum $album) {
		if (!$album->magToevoegen()) {
			$this->exit_http(403);
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
								$this->view = new JsonResponse(true);
								return;
							}
						} else {
							throw new CsrGebruikerException('Verwerken mislukt');
						}
					} else {
						throw new CsrGebruikerException('Opslaan mislukt');
					}
				} catch (CsrGebruikerException $e) {
					$this->view = new JsonResponse(array('error' => $e->getMessage()), 500);
					return;
				}
			} else {
				if ($poster) {
					// fall through
				} elseif ($uploader !== null) {
					$this->view = new JsonResponse(array('error' => $uploader->getError()), 500);
					return;
				}
			}
		}
		$this->view = new CsrLayoutPage($formulier);
		$this->view->addCompressedResources('fotoalbum');
	}

	public function bestaande(FotoAlbum $album) {
		if (!$album->magToevoegen()) {
			$this->exit_http(403);
		}
		$list = array();
		$files = @scandir($album->path . '_thumbs/');
		if ($files !== false) {
			foreach ($files as $filename) {
				if (endsWith($filename, '.jpg')) {
					$foto = new Foto($filename, $album, true);
					$obj['name'] = $foto->filename;
					$obj['size'] = $foto->filesize;
					$obj['type'] = $foto->mimetype;
					$obj['thumbnail'] = $foto->getThumbUrl();
					$list[] = $obj;
				}
			}
		}
		$this->view = new JsonResponse($list);
	}

	public function download($path) {
		$foto = Foto::fromFileName($path, true);
		if (!$foto->magBekijken()) {
			$this->exit_http(403);
		}
		if ($foto->exists()) {
			header('Content-Description: File Transfer');
			header('Content-Type: ' . $foto->mimetype);
			header('Content-Disposition: attachment; filename="' . $foto->filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . $foto->filesize);
			readfile($foto->directory . $foto->filename);
		}
		exit;
	}

	public function downloaden(FotoAlbum $album) {
		if (!$album->magDownloaden()) {
			$this->exit_http(403);
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

	public function hernoemen(FotoAlbum $album) {
		if (!$album->magAanpassen()) {
			$this->exit_http(403);
		}
		$naam = filter_input(INPUT_POST, 'Nieuwe_naam', FILTER_SANITIZE_STRING);
		if ($album !== null AND $this->model->hernoemAlbum($album, $naam)) {
			$this->view = new JsonResponse($album->getUrl());
		} else {
			$this->view = new JsonResponse('Fotoalbum hernoemen mislukt', 500);
		}
	}

	public function albumcover(FotoAlbum $album) {
		if (!$album->magAanpassen()) {
			$this->exit_http(403);
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$cover = new Foto($filename, $album);
		if ($cover->exists() AND $this->model->setAlbumCover($album, $cover)) {
			$this->view = new JsonResponse($album->getUrl() . '#' . $cover->getResizedUrl());
		} else {
			$this->view = new JsonResponse('Fotoalbum-cover instellen mislukt', 500);
		}
	}

	public function verwijderen(FotoAlbum $album) {
		if (!$album->magVerwijderen()) {
			$this->exit_http(403);
		}
		if ($album->isEmpty()) {
			if (1 === FotoAlbumModel::instance()->delete($album)) {
				setMelding('Fotoalbum verwijderen geslaagd', 1);
				$this->view = new JsonResponse(dirname($album->getUrl()));
			} else {
				setMelding('Fotoalbum verwijderen mislukt', -1);
				$this->view = new JsonResponse($album->getUrl());
			}
			return;
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (FotoModel::instance()->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit;
		} else {
			$this->view = new JsonResponse('Foto verwijderen mislukt', 500);
		}
	}

	public function roteren(FotoAlbum $album) {
		if (!$album->magAanpassen()) {
			$this->exit_http(403);
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		$degrees = (int)filter_input(INPUT_POST, 'rotation', FILTER_SANITIZE_NUMBER_INT);
		$foto->rotate($degrees);
		$this->view = new JsonResponse(true);
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->exit_http(403);
		}
		$query = iconv('utf-8', 'ascii//TRANSLIT', $this->getParam('q')); // convert accented characters to regular
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('replace(subdir, "é", "e") REGEXP ?', array($query . '[^/]*[/]{1}$'), null, 'subdir DESC', $limit) as $album) {
			/** @var FotoAlbum $album */
			$result[] = array(
				'url' => $album->getUrl(),
				'label' => $album->getParentName(),
				'value' => ucfirst($album->dirname)
			);
		}
		$this->view = new JsonResponse($result);
	}

	public function gettags(FotoAlbum $album) {
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			$this->exit_http(403);
		}
		// return all tags
		$tags = FotoTagsModel::instance()->getTags($foto);
		$this->view = new JsonResponse($tags->fetchAll());
	}

	public function addtag(FotoAlbum $album) {
		if (!$album->magToevoegen()) {
			$this->exit_http(403);
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			$this->exit_http(403);
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
			$this->view = new JsonResponse($tags->fetchAll());
		} else {
			$this->view = $formulier;
		}
	}

	public function removetag() {
		$refuuid = filter_input(INPUT_POST, 'refuuid', FILTER_SANITIZE_STRING);
		$keyword = filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING);
		if (!LoginModel::mag('P_ALBUM_MOD') AND !LoginModel::mag($keyword)) {
			$this->exit_http(403);
		}
		FotoTagsModel::instance()->removeTag($refuuid, $keyword);
		/** @var Foto $foto */
		$foto = FotoModel::instance()->retrieveByUUID($refuuid);
		if ($foto) {
			// return all tags
			$tags = FotoTagsModel::instance()->getTags($foto);
			$this->view = new JsonResponse($tags->fetchAll());
		} else {
			$this->view = new JsonResponse(array());
		}
	}

	public function raw_image($path) {
		//Extra check to prevent attacks
		if (!startsWith(realpath($path), realpath(PHOTOALBUM_PATH))) {
			$this->exit_http(403);
		}

		$image = Foto::fromFileName($path);
		if ($image === false || !$image->magBekijken()) {
			$this->exit_http(403);
		} else if (!$image->exists()) {
			$this->exit_http(403);
		}

		$file = fopen($image->getFullPath(), 'rb');
		header("Content-type: " . image_type_to_mime_type(exif_imagetype($image->getFullPath())));
		header("Content-Length: " . filesize($image->getFullPath()));
		header("Cache-Control: ", "max-age=2592000, public");
		fpassthru($file);
		exit;
	}

}
