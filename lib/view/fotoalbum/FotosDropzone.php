<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\formulier\Dropzone;
use CsrDelft\view\formulier\uploadvelden\ImageField;
use CsrDelft\view\Icon;

class FotosDropzone extends Dropzone {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, '/fotoalbum/uploaden/' . $album->subdir, new ImageField('afbeelding', 'Foto', null, null, array('image/jpeg')), '/fotoalbum');
		$this->titel = 'Fotos toevoegen aan: ' . ucfirst($album->dirname);
	}

	public function getBreadcrumbs() {
		return FotoAlbumBreadcrumbs::getBreadcrumbs($this->model, false, true);
	}

	public function view() {
		echo parent::view();
		echo '<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>';
		echo '<div class="float-right"><a class="btn" onclick="showExisting_' . $this->formId . '();$(this).remove();">' . Icon::getTag('photos') . ' Toon bestaande foto\'s in dit album</a></div>';
		// Uitleg foto's toevoegen
		$body = new CmsPaginaView(CmsPaginaModel::get('fotostoevoegen'));
		$body->view();
	}

}
