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
	}

	public function getBreadcrumbs() {
		return FotoAlbumBreadcrumbs::getBreadcrumbs($this->model, false, true);
	}

	public function view() {
		echo '<div class="card"><div class="card-header">Fotos toevoegen aan: ' .ucfirst($this->model->dirname). '</div><div class="card-body">';
		parent::view();
		echo '</div><div class="card-footer">';
		echo '<span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>';
		echo '<div class="float-right"><a href="#" onclick="showExisting_' . $this->formId . '();$(this).remove();">' . Icon::getTag('photos') . ' Toon bestaande foto\'s in dit album</a></div>';
		echo '</div></div>';
		// Uitleg foto's toevoegen
		$body = new CmsPaginaView(CmsPaginaModel::get('fotostoevoegen'));
		$body->view();
	}

}
