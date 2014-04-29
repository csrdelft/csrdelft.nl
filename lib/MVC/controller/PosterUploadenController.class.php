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
		$url = '/actueel/fotoalbum/';
		$fields['uploader'] = new FileField('/posters');
		$fields[] = new SubmitResetCancel($url);
		$formulier = new Formulier(null, 'posterForm', '/posteruploaden/toevoegen/', $fields);
		$formulier->titel = 'Poster uploaden';
		if ($this->isPosted() AND $formulier->validate()) {
			try {
				$jaar = date('Y') + 1;
				$map = PICS_PATH . '/fotoalbum/' . ($jaar - 1) . '-' . $jaar . '/Posters/'; // jaar vooruit
				if (!file_exists($map)) {
					$map = PICS_PATH . '/fotoalbum/' . ($jaar - 2) . '-' . ($jaar - 1) . '/Posters/'; // jaar terug
				}
				if (file_exists($map)) {
					if ($fields['uploader']->opslaan($map, $fields['uploader']->getModel()->bestandsnaam)) {
						$url .= substr($map, strpos($map, '/fotoalbum/') + 11);
						require_once 'fotoalbum.class.php';
						require_once 'fotoalbumcontent.class.php';
						$album = new Fotoalbum($url, $url);
						if (!$album->exists()) {
							setMelding('Fotoalbum bestaat niet', -1);
						}
						$album->verwerkFotos();
						invokeRefresh($url, 'Poster met succes opgeslagen', 1);
					} else {
						invokeRefresh($url, 'Poster opslaan mislukt', -1);
					}
				} else {
					invokeRefresh($url, 'Posters map bestaat niet: ' . $map, -1);
				}
			} catch (Exception $e) {
				invokeRefresh($url, 'Poster uploaden mislukt: ' . $e->getMessage(), -1);
			}
		}
		$this->view = new CsrLayoutPage($formulier);
	}

}
