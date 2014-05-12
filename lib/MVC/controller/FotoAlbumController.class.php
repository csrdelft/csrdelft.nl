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
	 * Als deze regexp matched is het album alleen voor leden
	 * @var string
	 */
	private static $alleenLeden = '/(intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé)/i';
	/**
	 * Als deze regexp matched is het album alleen voor DéDé
	 * @var string
	 */
	private static $alleenVrouwen = '/(DéDé-privé|DeDe-prive|vrouwen-only)/i';

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'bekijken' => 'P_NOBODY',
				'downloaden' => 'P_LOGGED_IN',
				'verwerken' => 'P_LEDEN_READ',
				'albumcover' => 'P_DOCS_MOD'
			);
		} else {
			$this->acl = array(
				'verwijderen' => 'P_DOCS_MOD',
				'hernoemen' => 'P_DOCS_MOD'
			);
		}
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if (!array_key_exists($this->action, $this->acl)) {
			$this->action = 'bekijken';
			$path = array_filter($this->getParams(2));
		} else {
			$path = array_filter($this->getParams(4));
		}
		$map = new Map();
		$map->locatie = PICS_PATH . '/';
		$naam = 'fotoalbum';
		if (!empty($path)) {
			$map->locatie .= 'fotoalbum/';
			$naam = urldecode(array_pop($path));
			if (!empty($path)) {
				$map->locatie .= urldecode(implode('/', $path)) . '/';
			}
		}
		if (!self::magBekijken($map->locatie)) {
			$this->geentoegang();
		}
		$this->performAction(array($map, $naam));
	}

	public static function magBekijken($path) {
		if (LoginLid::mag('P_LEDEN_READ')) {
			if (preg_match(self::$alleenVrouwen, $path)) { // Deze foto's alleen voor DéDé
				if (LoginLid::instance()->getLid()->getGeslacht() == 'v') {
					return true;
				}
				return false;
			}
			return true;
		} else {
			if (preg_match(self::$alleenLeden, $path)) {
				return false; // Deze foto's niet voor gewoon volk
			}
			if (preg_match(self::$alleenVrouwen, $path)) {
				return false; // Deze foto's alleen voor DéDé
			}
			return true;
		}
	}

	public function bekijken(Map $map, $naam) {
		$album = FotoAlbumModel::getFotoAlbum($map, $naam);
		$body = new FotoAlbumView($album);
		if (LoginLid::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
			$this->view->zijkolom = false;
		} else { // uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('fotoalbum.css');
		$this->view->addStylesheet('jquery.prettyPhoto-3.1.5.css?');
		$this->view->addScript('jquery/plugins/jquery.prettyPhoto-3.1.5.min.js?');
	}

	public function verwerken(Map $map, $naam) {
		//define('RESIZE_OUTPUT', null);
		if (defined('RESIZE_OUTPUT')) {
			echo '<h1>Fotoalbum verwerken: ' . $naam . '</h1>';
			echo 'Dit kan even duren<br />';
			flush();
		}
		$album = FotoAlbumModel::getFotoAlbum($map, $naam);
		FotoAlbumModel::verwerkFotos($album);
		if (defined('RESIZE_OUTPUT')) {
			exit;
		} else {
			invokeRefresh($album->getSubDir(), 'Fotoalbum ' . $naam . ' succesvol verwerkt', 1);
		}
	}

	public function downloaden(Map $map, $naam) {
		header('Content-type: application/x-tar');
		header('Content-Disposition: attachment; filename="' . $naam . '.tar"');
		$album = FotoAlbumModel::getFotoAlbum($map, $naam);
		$fotos = $album->getFotos();
		set_time_limit(0);
		$cmd = "tar cC " . escapeshellarg($album->locatie);
		foreach ($fotos as $foto) {
			$cmd .= ' ' . escapeshellarg($foto->bestandsnaam);
		}
		$fh = popen($cmd, 'r');
		while (!feof($fh)) {
			print fread($fh, 8192);
		}
		pclose($fh);
		exit;
	}

	public function verwijderen(Map $map, $naam) {
		$album = FotoAlbumModel::getFotoAlbum($map, $naam);
		$bestandsnaam = filter_input(INPUT_POST, 'foto', FILTER_SANITIZE_STRING);
		$foto = new Foto($album, $bestandsnaam);
		if (FotoAlbumModel::verwijderFoto($foto)) {
			echo '<div id="' . md5($bestandsnaam) . '" class="remove"></div>';
		} else {
			setMelding('Foto verwijderen mislukt', -1);
		}
		exit;
	}

	public function hernoemen(Map $map, $naam) {
		$album = FotoAlbumModel::getFotoAlbum($map, $naam);
		$nieuw = filter_input(INPUT_POST, 'naam', FILTER_SANITIZE_STRING);
		if (!preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $nieuw)) {
			throw new Exception('Ongeldige albumnaam');
		}
		if (FotoAlbumModel::hernoemAlbum($album, $nieuw)) {
			echo '<div id="' . md5($naam) . '" class="albumname">' . $nieuw . '</div>';
		} else {
			setMelding('Fotoalbum hernoemen mislukt', -1);
		}
		exit;
	}

	public function albumcover(Map $map, $naam) {
		$album = FotoAlbumModel::getFotoAlbum($map, '');
		$foto = new Foto($album, $naam);
		if (FotoAlbumModel::setAlbumCover($album, $foto)) {
			invokeRefresh($album->getSubDir(), 'Fotoalbum-cover succesvol ingesteld', 1);
		} else {
			exit;
			invokeRefresh($album->getSubDir(), 'Fotoalbum-cover instellen mislukt', -1);
		}
	}

}
