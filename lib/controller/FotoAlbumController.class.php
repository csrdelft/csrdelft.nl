<?php

require_once 'view/FotoAlbumView.class.php';

/**
 * FotoAlbumController.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het fotoalbum.
 */
class FotoAlbumController extends AclController {

	public function __construct($query) {
		parent::__construct($query, FotoAlbumModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'bekijken'	 => 'P_ALBUM_READ',
				'download'	 => 'P_ALBUM_READ',
				'downloaden' => 'P_ALBUM_DOWN',
				'verwerken'	 => 'P_ALBUM_MOD',
				'uploaden'	 => 'P_ALBUM_ADD',
				'zoeken'	 => 'P_LEDEN_READ'
			);
		} else {
			$this->acl = array(
				'albumcover'	 => 'P_ALBUM_ADD',
				'verwijderen'	 => 'P_ALBUM_ADD',
				'hernoemen'		 => 'P_ALBUM_ADD',
				'roteren'		 => 'P_ALBUM_ADD',
				'toevoegen'		 => 'P_ALBUM_ADD',
				'bestaande'		 => 'P_ALBUM_ADD',
				'uploaden'		 => 'P_ALBUM_ADD',
				'gettags'		 => 'P_LEDEN_READ',
				'addtag'		 => 'P_LEDEN_READ',
				'removetag'		 => 'P_LEDEN_READ'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if (!array_key_exists($this->action, $this->acl)) {
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
		$path = PICS_PATH . urldecode(implode('/', $path));
		if ($this->action === 'download') {
			parent::performAction(array($path));
			return;
		}
		$album = $this->model->getFotoAlbum($path);
		if (!$album) {
			setMelding('Fotoalbum bestaat niet' . (DEBUG ? ': ' . $path : ''), -1);
			redirect('/fotoalbum');
		}
		$args[] = $album;
		parent::performAction($args);
	}

	public function bekijken(FotoAlbum $album) {
		if ($album->dirname === 'Posters') {
			$album->orderByDateModified();
		}
		$body = new FotoAlbumView($album);
		// uitgelogd heeft nieuwe layout
		if (LoginModel::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addCompressedResources('fotoalbum');
	}

	public function verwerken(FotoAlbum $album) {
		if ($album->dirname === 'fotoalbum') {
			setMelding('Niet het complete fotoalbum verwerken', -1);
			redirect($album->getUrl());
		}
		$this->model->verwerkFotos($album);
		redirect($album->getUrl());
	}

	public function toevoegen(FotoAlbum $album) {
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($this->isPosted() AND $formulier->validate()) {
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
		$poster = $album->dirname === 'Posters';
		if ($poster) {
			$formulier = new PosterUploadForm($album);
			$uploader = $formulier->findByName('afbeelding');
		} else {
			$formulier = new FotosDropzone($album);
			$uploader = $formulier->getPostedUploader();
		}
		if ($this->isPosted()) {
			if ($formulier->validate()) {
				try {
					if ($poster) {
						$filename = $formulier->findByName('posternaam')->getValue() . '.jpg';
						if (strpos($filename, 'folder') !== false) {
							throw new Exception('Albumcover niet toegestaan');
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
							throw new Exception('Verwerken mislukt');
						}
					} else {
						throw new Exception('Opslaan mislukt');
					}
				} catch (Exception $e) {
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
		$foto = new Afbeelding($path);
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
		if (!LoginModel::mag('P_ALBUM_MOD') AND ! $album->isOwner()) {
			$this->geentoegang();
		}
		$naam = filter_input(INPUT_POST, 'Nieuwe_naam', FILTER_SANITIZE_STRING);
		if ($album !== null AND $this->model->hernoemAlbum($album, $naam)) {
			$this->view = new JsonResponse($album->getUrl());
		} else {
			$this->view = new JsonResponse('Fotoalbum hernoemen mislukt', 500);
		}
	}

	public function albumcover(FotoAlbum $album) {
		if (!LoginModel::mag('P_ALBUM_MOD') AND ! $album->isOwner()) {
			$this->geentoegang();
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
		if (!$foto->exists() OR ! LoginModel::mag('P_ALBUM_DEL') AND ! $foto->isOwner()) {
			$this->geentoegang();
		}
		if (FotoModel::instance()->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit;
		} else {
			$this->view = new JsonResponse('Foto verwijderen mislukt', 500);
		}
	}

	public function roteren(FotoAlbum $album) {
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists() OR ! LoginModel::mag('P_ALBUM_MOD') AND ! $foto->isOwner()) {
			$this->geentoegang();
		}
		$degrees = (int) filter_input(INPUT_POST, 'rotation', FILTER_SANITIZE_NUMBER_INT);
		$foto->rotate($degrees);
		$this->view = new JsonResponse(true);
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$query = iconv('utf-8', 'ascii//TRANSLIT', $this->getParam('q')); // convert accented characters to regular 
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int) $this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('replace(subdir, "é", "e") REGEXP ?', array($query . '[^/]*[/]{1}$'), null, 'subdir DESC', $limit) as $album) {
			$result[] = array(
				'url'	 => $album->getUrl(),
				'label'	 => $album->getParentName(),
				'value'	 => ucfirst($album->dirname)
			);
		}
		$this->view = new JsonResponse($result);
	}

	public function gettags(FotoAlbum $album) {
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			$this->geentoegang();
		}
		$tags = FotoTagsModel::instance()->getTags($foto)->fetchAll();
		$this->view = new JsonResponse($tags);
	}

	public function addtag(FotoAlbum $album) {
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			$this->geentoegang();
		}
		$formulier = new FotoTagToevoegenForm($foto);
		if ($this->isPosted() AND $formulier->validate()) {
			$uid = $formulier->findByName('uid')->getValue();
			$x = $formulier->findByName('x')->getValue();
			$y = $formulier->findByName('y')->getValue();
			$size = $formulier->findByName('size')->getValue();
			$tag = FotoTagsModel::instance()->addTag($foto, $uid, $x, $y, $size);
			$this->view = new JsonResponse($tag);
			return;
		}
		$this->view = $formulier;
	}

	public function removetag() {
		$refuuid = filter_input(INPUT_POST, 'refuuid', FILTER_SANITIZE_STRING);
		$keyword = filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING);
		if (!LoginModel::mag('P_ALBUM_MOD') AND ! LoginModel::mag($keyword)) {
			$this->geentoegang();
		}
		FotoTagsModel::instance()->removeTag($refuuid, $keyword);
		$this->view = new JsonResponse(true);
	}

}
