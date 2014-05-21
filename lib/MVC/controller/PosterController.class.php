<?php

require_once 'MVC/controller/FotoAlbumController.class.php';

/**
 * PosterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class PosterController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		$this->acl = array(
			'uploaden' => 'P_LEDEN_READ'
		);
		$this->action = 'uploaden';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function uploaden() {
		$dirs = array();
		$glob = glob(PICS_PATH . '/fotoalbum/*', GLOB_ONLYDIR);
		if (!is_array($glob)) {
			setMelding(null, 'Fotoalbum is leeg', -1);
		} else {
			foreach ($glob as $path) {
				$parts = explode('/', $path);
				$name = end($parts);
				if (!startsWith($name, '_')) {
					$dirs[$name] = $name;
				}
			}
		}
		$fields['album'] = new SelectField('album', null, 'Album', array_reverse($dirs));
		$fields['naam'] = new RequiredTextField('naam', null, 'Posternaam', 50, 5);
		$fields['uploader'] = new FileField('/posters', null, array('image/jpeg'));
		$fields[] = new Subkopje('Alleen jpeg afbeeldingen.');
		$fields['knoppen'] = new SubmitResetCancel('/actueel/fotoalbum/');
		$fields['knoppen']->resetIcon = null;
		$fields['knoppen']->resetText = null;
		$formulier = new Formulier(null, 'posterForm', '/poster/uploaden/');
		$formulier->addFields($fields);
		$formulier->titel = 'Poster uploaden';
		if ($this->isPosted() AND $formulier->validate()) {
			try {
				$path = PICS_PATH . '/fotoalbum/' . $fields['album']->getValue();
				if (file_exists($path . '/Posters/')) {
					$filenaam = $fields['naam']->getValue() . '.jpg';
					if ($fields['uploader']->opslaan($path . '/Posters/', $filenaam)) {
						$map = new Map();
						$map->locatie = $path . '/';
						$album = FotoAlbumModel::getFotoAlbum($map, 'Posters');
						if ($album === null) {
							invokeRefresh(null, 'Fotoalbum bestaat niet', -1);
						}
						FotoAlbumModel::verwerkFotos($album);
						invokeRefresh($album->getUrl() . '#' . direncode($filenaam), 'Poster met succes opgeslagen', 1);
					} else {
						invokeRefresh(null, 'Poster opslaan mislukt', -1);
					}
				} else {
					invokeRefresh(null, 'Posters map bestaat niet in album: ' . $fields['album']->getValue(), -1);
				}
			} catch (Exception $e) {
				invokeRefresh(null, 'Poster uploaden mislukt: ' . $e->getMessage(), -1);
			}
		}
		$this->view = new CsrLayoutPage($formulier);
	}

}
