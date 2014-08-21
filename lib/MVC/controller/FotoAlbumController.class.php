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
				'verwerken'	 => 'P_ALBUM_ADMIN',
				'toevoegen'	 => 'P_ALBUM_ADD'
			);
		} else {
			$this->acl = array(
				'albumcover'	 => 'P_ALBUM_MOD',
				'verwijderen'	 => 'P_ALBUM_ADMIN',
				'hernoemen'		 => 'P_ALBUM_MOD',
				'uploaden'		 => 'P_ALBUM_ADD'
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
			invokeRefresh(CSR_ROOT . '/fotoalbum', 'Fotoalbum bestaat niet', -1);
		}
		$args[] = $album;
		parent::performAction($args);
	}

	public static function magBekijken($path) {
		if (!startsWith($path, PICS_PATH . 'fotoalbum/')) {
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
			$this->view->zijkolom = false;
		} else { // uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('/layout/js/jquery/plugins/jquery.prettyPhoto.min.css');
		$this->view->addScript('/layout/js/jquery/plugins/jquery.prettyPhoto.min.js');
		$this->view->addStylesheet('/layout/css/fotoalbum.css');
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
			invokeRefresh(CSR_ROOT . '/' . $album->getSubDir(), 'Fotoalbum ' . $album->dirname . ' succesvol verwerkt', 1);
		}
	}

	public function toevoegen(FotoAlbum $album) {
		$this->uploaden($album);
	}

	public function uploaden(FotoAlbum $album) {
		if ($album->dirname === 'Posters') {
			$formulier = new PosterUploadForm($album);
			$msg = 'Poster';
		} else {
			$formulier = new FotoUploadForm($album);
			$msg = 'Foto';
		}
		if ($this->isPosted() AND $formulier->validate()) {
			try {
				$uploader = $formulier->findByName('foto');
				if ($album->dirname === 'Posters') {
					$filenaam = $formulier->findByName('posternaam')->getValue() . '.jpg';
				} else {
					$filenaam = $uploader->getModel()->filename;
				}
				$subalbum = $formulier->findByName('subalbum')->getValue();
				if ($subalbum != '') {
					$album->path .= $subalbum . '/';
				}
				if ($uploader->opslaan($album->path, $filenaam)) {
					FotoAlbumModel::verwerkFotos($album);
					setMelding($msg . ' met succes toegevoegd', 1); //TODO: $album->getUrl() . '#' . direncode($filenaam)
				} else {
					setMelding($msg . ' toevoegen mislukt', -1);
				}
			} catch (Exception $e) {
				setMelding($msg . ' toevoegen mislukt: ' . $e->getMessage(), -1);
				DebugLogModel::instance()->log(get_called_class(), $this->action, array($album->path, $album->dirname), $e);
			}
		}
		$this->view = $formulier;
	}

	public function downloaden(FotoAlbum $album) {
		header('Content-type: application/x-tar');
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
			echo '<div id="' . md5($album->dirname) . '" class="albumname">' . $naam . '</div>';
		} else {
			setMelding('Fotoalbum hernoemen mislukt', -1);
		}
		exit;
	}

	public function verwijderen(FotoAlbum $album) {
		$naam = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		if ($album !== null AND FotoAlbumModel::verwijderFoto(new Foto($album, $naam))) {
			echo '<div id="' . md5($naam) . '" class="remove"></div>';
		} else {
			setMelding('Foto verwijderen mislukt', -1);
		}
		exit;
	}

	public function albumcover(FotoAlbum $album) {
		$naam = filter_input(INPUT_POST, 'cover', FILTER_SANITIZE_STRING);
		if (FotoAlbumModel::setAlbumCover($album, new Foto($album, $naam))) {
			invokeRefresh(CSR_ROOT . '/' . $album->getSubDir(), 'Fotoalbum-cover succesvol ingesteld', 1);
		} else {
			invokeRefresh(CSR_ROOT . '/fotoalbum', 'Fotoalbum-cover instellen mislukt', -1);
		}
	}

}
