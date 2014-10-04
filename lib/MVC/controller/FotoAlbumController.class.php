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

	/**
	 * Als deze regexp matched is het album alleen voor leden toegankelijk
	 * @var string
	 */
	private static $alleenLeden = '/(intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';
	/**
	 * Als deze regexp matched is het album alleen voor vrouwen toegankelijk
	 * @var string
	 */
	private static $alleenVrouwen = '/(DéDé-privé|DeDe-prive|vrouwen-only)/i';
	/**
	 * Als deze regexp matched is het album alleen voor mannen toegankelijk
	 * @var string
	 */
	private static $alleenMannen = '/(men-only|mannen-only)/i';

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'bekijken'	 => 'P_ALBUM_READ',
				'downloaden' => 'P_ALBUM_DOWN',
				'verwerken'	 => 'P_ADMIN',
				'uploaden'	 => 'P_ALBUM_ADD'
			);
		} else {
			$this->acl = array(
				'albumcover'	 => 'P_ALBUM_MOD',
				'verwijderen'	 => 'P_ALBUM_DEL',
				'hernoemen'		 => 'P_ALBUM_MOD',
				'toevoegen'		 => 'P_ALBUM_ADD',
				'uploaden'		 => 'P_ALBUM_ADD',
				'roteren'		 => 'P_ALBUM_ADD'
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
		} else {
			$path = $this->getParams(3);
		}
		$path = PICS_PATH . urldecode(implode('/', $path));
		$album = FotoAlbumModel::getFotoAlbum($path);
		if (!$album) {
			SimpleHTML::setMelding('Fotoalbum bestaat niet!', -1);
			if (DEBUG) {
				SimpleHTML::setMelding($path, 0);
			}
			redirect(CSR_ROOT . '/fotoalbum');
		}
		$args[] = $album;
		parent::performAction($args);
	}

	public static function magBekijken($path) {
		$mapnaam = basename($path);
		if (startsWith($mapnaam, '_') OR ! startsWith($path, PICS_PATH . 'fotoalbum/')) {
			return false;
		}
		if (LoginModel::mag('P_LEDEN_READ')) {
			if (preg_match(self::$alleenVrouwen, $path)) { // Deze foto's alleen voor vrouwen
				if (LoginModel::instance()->getLid()->getGeslacht() == 'v') {
					return true;
				}
				return false;
			}
			if (preg_match(self::$alleenMannen, $path)) { // Deze foto's alleen voor mannen
				if (LoginModel::instance()->getLid()->getGeslacht() == 'm') {
					return true;
				}
				return false;
			}
			return true;
		} else {
			if (preg_match(self::$alleenLeden, $path)) {
				return false; // Deze foto's alleen voor leden
			}
			if (preg_match(self::$alleenVrouwen, $path)) {
				return false; // Deze foto's alleen voor vrouwen
			}
			if (preg_match(self::$alleenMannen, $path)) {
				return false; // Deze foto's alleen voor mannen
			}
			return true;
		}
	}

	public function bekijken(FotoAlbum $album) {
		$body = new FotoAlbumView($album);
		if (LoginModel::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
			$this->view->zijbalk = false;
		} else { // uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('/layout/js/jquery/plugins/jquery.prettyPhoto');
		$this->view->addScript('/layout/js/jquery/plugins/jquery.prettyPhoto');
		$this->view->addStylesheet('/layout/css/fotoalbum');
	}

	public function verwerken(FotoAlbum $album) {
		//define('RESIZE_OUTPUT', null);
		if (defined('RESIZE_OUTPUT')) {
			echo '<h1>Fotoalbum verwerken: ' . $album->dirname . '</h1>';
			echo 'Dit kan even duren<br />';
			flush();
		}
		FotoAlbumModel::verwerkFotos($album);
		if (defined('RESIZE_OUTPUT')) {
			exit;
		} else {
			SimpleHTML::setMelding('Fotoalbum ' . $album->dirname . ' succesvol verwerkt', 1);
			redirect(CSR_ROOT . '/' . $album->getSubDir());
		}
	}

	public function toevoegen(FotoAlbum $album) {
		$formulier = new FotoAlbumToevoegenForm($album);
		if ($this->isPosted() AND $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path .= $subalbum . '/';
			if (!$album->exists()) {
				mkdir($album->path);
				chmod($album->path, 0755);
			} else {
				SimpleHTML::setMelding('Fotoalbum bestaat al', 0);
			}
			$this->view = new JsonResponse(true);
		}
		$this->view = $formulier;
	}

	public function uploaden(FotoAlbum $album) {
		if ($album->dirname === 'Posters') {
			$formulier = new PosterUploadForm($album);
		} else {
			$formulier = new FotosDropzone($album);
		}
		if ($this->isPosted()) {
			if ($formulier->validate()) {
				if ($album->dirname === 'Posters') {
					$uploader = $formulier->findByName('afbeelding');
					$filename = $formulier->findByName('posternaam')->getValue() . '.jpg';
				} else {
					$uploader = $formulier->getPostedUploader();
					$filename = $uploader->getModel()->filename;
				}
				if ($uploader->opslaan($album->path, $filename)) {
					FotoAlbumModel::verwerkFotos($album);
					if ($album->dirname === 'Posters') {
						redirect($album->getUrl());
					}
					exit;
				} else {
					$this->view = new JsonResponse(array('error' => $uploader->getError()), 500);
				}
			} else {
				$list = array();
				$files = scandir($album->path . '_thumbs/');
				if ($files !== false) {
					foreach ($files as $file) {
						if (endsWith($file, '.jpg')) {
							$foto = new Foto($album, $file);
							$foto->filesize = filesize($foto->getPad());
							$obj['name'] = $foto->filename;
							$obj['size'] = $foto->filesize;
							$obj['type'] = 'image/jpeg';
							$obj['thumb'] = $foto->getThumbURL();
							$list[] = $obj;
						}
					}
				}
				$this->view = new JsonResponse($list);
			}
		} else {
			$this->view = new CsrLayoutPage($formulier);
			$this->view->addStylesheet('/layout/css/dropzone');
			$this->view->addScript('/layout/js/dropzone');
		}
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
		$naam = filter_input(INPUT_POST, 'Nieuwe_naam', FILTER_SANITIZE_STRING);
		if ($album !== null AND FotoAlbumModel::hernoemAlbum($album, $naam)) {
			SimpleHTML::setMelding('Fotoalbum succesvol hernoemd', 1);
		} else {
			SimpleHTML::setMelding('Fotoalbum hernoemen mislukt', -1);
		}
		$this->view = new JsonResponse(true);
	}

	public function verwijderen(FotoAlbum $album) {
		$naam = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		if ($album !== null AND FotoAlbumModel::verwijderFoto(new Foto($album, $naam))) {
			echo '<div id="' . md5($naam) . '" class="remove"></div>';
		} else {
			SimpleHTML::setMelding('Foto verwijderen mislukt', -1);
		}
		exit; //TODO: JsonResponse
	}

	public function albumcover(FotoAlbum $album) {
		$naam = filter_input(INPUT_POST, 'cover', FILTER_SANITIZE_STRING);
		if (FotoAlbumModel::setAlbumCover($album, new Foto($album, $naam))) {
			SimpleHTML::setMelding('Fotoalbum-cover succesvol ingesteld', 1);
		} else {
			SimpleHTML::setMelding('Fotoalbum-cover instellen mislukt', -1);
		}
		$this->view = new JsonResponse(true);
	}

	public function roteren(FotoAlbum $album) {
		$degrees = filter_input(INPUT_POST, 'rotate', FILTER_SANITIZE_NUMBER_INT);
		$naam = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($album, $naam);
		$foto->rotate($degrees);
		$this->view = new JsonResponse(true);
	}

}
