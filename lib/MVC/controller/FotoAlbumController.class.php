<?php

require_once 'MVC/model/FotoAlbumModel.class.php';
require_once 'MVC/view/FotoAlbumView.class.php';

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
				'downloaden' => 'P_ALBUM_DOWN',
				'verwerken'	 => 'P_ALBUM_MOD',
				'uploaden'	 => 'P_ALBUM_ADD',
				'zoeken'	 => 'P_ALBUM_READ'
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
		} elseif ($this->action === 'zoeken' OR $this->action === 'index') {
			parent::performAction($this->getParams(3));
			return;
		} else {
			$path = $this->getParams(3);
		}
		$path = PICS_PATH . urldecode(implode('/', $path));
		$album = $this->model->getFotoAlbum($path);
		if (!$album) {
			setMelding('Fotoalbum bestaat niet!', -1);
			if (DEBUG) {
				setMelding($path, 0);
			}
			redirect(CSR_ROOT . '/fotoalbum');
		}
		$args[] = $album;
		parent::performAction($args);
	}

	public function bekijken(FotoAlbum $album) {
		$body = new FotoAlbumView($album);
		if (LoginModel::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
			//$this->view->zijbalk = false;
		} else {
			// uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addCompressedResources('fotoalbum');
	}

	public function verwerken(FotoAlbum $album) {
		if ($album->dirname === 'fotoalbum') {
			setMelding('Niet het complete fotoalbum verwerken', -1);
			redirect($album->getUrl());
		}
		//define('RESIZE_OUTPUT', null);
		if (defined('RESIZE_OUTPUT')) {
			echo '<h1>Fotoalbum verwerken: ' . $album->dirname . '</h1>';
			echo 'Dit kan even duren<br />';
			flush();
		}
		$this->model->verwerkFotos($album);
		if (defined('RESIZE_OUTPUT')) {
			exit;
		} else {
			setMelding($album->dirname . ' succesvol verwerkt', 1);
			redirect($album->getUrl());
		}
	}

	public function toevoegen(FotoAlbum $album) {
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($this->isPosted() AND $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path .= $subalbum . '/';
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
								redirect($album->getUrl());
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
		$files = scandir($album->path . '_thumbs/');
		if ($files !== false) {
			foreach ($files as $filename) {
				if (endsWith($filename, '.jpg')) {
					$foto = new Foto($filename, $album);
					$foto->filesize = filesize($foto->getFullPath());
					$obj['name'] = $foto->filename;
					$obj['size'] = $foto->filesize;
					$obj['type'] = 'image/jpeg';
					$obj['thumb'] = $foto->getThumbUrl();
					$list[] = $obj;
				}
			}
		}
		$this->view = new JsonResponse($list);
	}

	public function downloaden(FotoAlbum $album) {
		header('Content-Type: application/x-tar');
		header('Content-Disposition: attachment; filename="' . $album->dirname . '.tar"');
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
			echo $album->getUrl();
			exit;
		} else {
			$this->view = new JsonResponse('Fotoalbum hernoemen mislukt', 503);
		}
	}

	public function albumcover(FotoAlbum $album) {
		if (!LoginModel::mag('P_ALBUM_MOD') AND ! $album->isOwner()) {
			$this->geentoegang();
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		if ($this->model->setAlbumCover($album, new Foto($filename, $album))) {
			$this->view = new JsonResponse(true);
		} else {
			$this->view = new JsonResponse('Fotoalbum-cover instellen mislukt', 503);
		}
	}

	public function verwijderen(FotoAlbum $album) {
		if ($album->isEmpty()) {
			FotoAlbumModel::instance()->delete($album);
			$this->view = new JsonResponse(true);
			return;
		}
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!LoginModel::mag('P_ALBUM_DEL') AND ! $foto->isOwner()) {
			$this->geentoegang();
		}
		if (FotoModel::instance()->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit;
		} else {
			$this->view = new JsonResponse('Foto verwijderen mislukt', 503);
		}
	}

	public function roteren(FotoAlbum $album) {
		$filename = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($filename, $album);
		if (!LoginModel::mag('P_ALBUM_MOD') AND ! $foto->isOwner()) {
			$this->geentoegang();
		}
		$degrees = (int) filter_input(INPUT_POST, 'rotation', FILTER_SANITIZE_NUMBER_INT);
		$foto->rotate($degrees);
		$this->view = new JsonResponse(true);
	}

	public function zoeken($query = null) {
		if ($query === null) {
			$this->geentoegang();
		}
		$result = array();
		foreach ($this->model->find('subdir LIKE ?', array('%' . $query . '%')) as $album) {
			if (stripos($album->dirname, $query) !== false AND $album->magBekijken()) {
				$result[] = array(
					'url'	 => $album->getUrl(),
					'value'	 => ucfirst($album->dirname) . '<span class="lichtgrijs"> - ' . $album->getParentName() . '</span>'
				);
			}
		}
		$this->view = new JsonResponse($result);
	}

}
