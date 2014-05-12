<?php

require_once 'MVC/model/FotoAlbumModel.class.php';
require_once 'MVC/view/FotoAlbumView.class.php';

/**
 * FotoAlbumController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het fotoalbum.
 */
class FotoAlbumController extends Controller {

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
		$this->action = 'bekijken';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
			if ($this->action === 'verwerken') {
				$path = array_filter($this->getParams(4));
			} else {
				$path = array_filter($this->getParams(2));
			}
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

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'verwijderen':
			case 'hernoemen':
			case 'verplaatsen':
				return $this->isPosted() AND LoginLid::mag('P_ADMIN');

			case 'verwerken':
				return LoginLid::mag('P_LEDEN_READ');

			default:
				$this->action = 'bekijken';
				return true;
		}
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
		$album = new FotoAlbum($map, $naam);
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
		$album = new FotoAlbum($map, $naam);
		$album->verwerkFotos();
		if (defined('RESIZE_OUTPUT')) {
			exit;
		} else {
			invokeRefresh($album->getSubDir(), 'Fotoalbum ' . $naam . ' succesvol verwerkt', 1);
		}
	}

}
