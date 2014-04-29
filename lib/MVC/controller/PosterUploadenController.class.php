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
				$map = ($jaar - 1) . '-' . $jaar . '/Posters'; // jaar vooruit
				if (!file_exists(PICS_PATH . '/fotoalbum/' . $map)) {
					$map = ($jaar - 2) . '-' . ($jaar - 1) . '/Posters'; // jaar terug
				}
				if (file_exists(PICS_PATH . '/fotoalbum/' . $map)) {
					if ($fields['uploader']->opslaan(PICS_PATH . '/fotoalbum/' . $map . '/', $fields['uploader']->getModel()->bestandsnaam)) {
						$url .= $map;
						require_once 'fotoalbum.class.php';
						require_once 'fotoalbumcontent.class.php';
						$album = new Fotoalbum($map, $map);
						if (!$album->exists()) {
							invokeRefresh(null, 'Fotoalbum bestaat niet: ' . $album->getFullpath(), -1);
						}
						define('RESIZE_OUTPUT', null);
						$album->verwerkFotos();
						invokeRefresh($url, 'Poster met succes opgeslagen', 1);
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
