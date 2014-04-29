<?php

/**
 * PosterUploadenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class PosterUploadenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, FunctiesModel::instance());
		$this->acl = array(
			'toevoegen' => 'P_LEDEN_READ'
		);
		$this->action = 'toevoegen';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function toevoegen() {
		foreach (glob(PICS_PATH . '/fotoalbum/*', GLOB_ONLYDIR) as $path) {
			$parts = explode('/', $path);
			$name = end($parts);
			if (!startsWith($name, '_')) {
				$dirs[$name] = $name;
			}
		}
		$fields['album'] = new SelectField('album', null, 'Album', array_reverse($dirs));
		$fields['uploader'] = new FileField('/posters');
		$fields['knoppen'] = new SubmitResetCancel('/actueel/fotoalbum/');
		$fields['knoppen']->resetIcon = null;
		$fields['knoppen']->resetText = null;
		$formulier = new Formulier(null, 'posterForm', '/posteruploaden/toevoegen/', $fields);
		$formulier->titel = 'Poster uploaden';
		if ($this->isPosted() AND $formulier->validate()) {
			try {
				$map = PICS_PATH . '/fotoalbum/' . $fields['album']->getValue() . '/Posters/';
				if (file_exists($map)) {
					if ($fields['uploader']->opslaan($map, $fields['uploader']->getModel()->bestandsnaam)) {
						$map = $fields['album']->getValue() . '/Posters';
						require_once 'fotoalbum.class.php';
						require_once 'fotoalbumcontent.class.php';
						$album = new Fotoalbum($map, $map);
						if (!$album->exists()) {
							invokeRefresh(null, 'Fotoalbum bestaat niet: ' . $album->getFullpath(), -1);
						}
						$album->verwerkFotos();
						invokeRefresh('/actueel/fotoalbum/' . $map, 'Poster met succes opgeslagen', 1);
					} else {
						invokeRefresh(null, 'Poster opslaan mislukt', -1);
					}
				} else {
					invokeRefresh(null, 'Posters map bestaat niet: ' . PICS_PATH . '/fotoalbum/' . $map, -1);
				}
			} catch (Exception $e) {
				invokeRefresh(null, 'Poster uploaden mislukt: ' . $e->getMessage(), -1);
			}
		}
		$this->view = new CsrLayoutPage($formulier);
	}

}
