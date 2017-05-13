<?php
namespace CsrDelft\view;

use CsrDelft\Icon;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\FotoAlbumModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\view\formulier\Dropzone;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\RequiredFileNameField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\formulier\uploadvelden\ImageField;
use CsrDelft\view\formulier\uploadvelden\RequiredImageField;
use function CsrDelft\getMelding;


/**
 * FotoAlbumView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De views van het fotoalbum.
 */
class FotoAlbumView extends SmartyTemplateView {

	public function __construct(FotoAlbum $fotoalbum) {
		parent::__construct($fotoalbum);
	}

	function getTitel() {
		return ucfirst($this->model->dirname);
	}

	function view() {
		echo getMelding();
		$this->smarty->assign('album', $this->model);
		$this->smarty->display('fotoalbum/album.tpl');
	}

	public function getBreadcrumbs($dropdown = true, $self = false) {
		return $this->getBreadcrumbsDropdown($dropdown, $self);
	}

	private function getBreadcrumbsDropdown($dropdown = false, $self = true) {
		$breadcrumbs = '<a href="/fotoalbum" title="Fotoalbum"><span class="fa fa-camera module-icon"></span></a>';
		$mappen = explode('/', $this->model->subdir);
		$subdir = 'fotoalbum/';
		$first = true;
		foreach ($mappen as $albumnaam) {
			if ($first) {
				$first = false;
				// module icon
			} elseif ($albumnaam === '') {
				// trailing slash: allerlaatste
				break;
			} else {
				if ($albumnaam === $this->model->dirname) {
					// laatste
					if ($dropdown) {
						$breadcrumbs .= ' » ' . FotoAlbumView::getDropDown(PHOTOS_PATH . $subdir, $albumnaam);
						break;
					} elseif (!$self) {
						// alleen parent folders tonen
						break;
					}
				}
				$subdir .= $albumnaam . '/';
				$breadcrumbs .= ' » <a href="/' . $subdir . '">' . ucfirst($albumnaam) . '</a>';
			}
		}
		return $breadcrumbs;
	}

	private function getDropDown($subdir, $albumnaam) {
		$parent = FotoAlbumModel::instance()->getFotoAlbum($subdir);
		if (!$parent) {
			return '';
		}
		$albums = $parent->getSubAlbums();
		$dropdown = '<select onchange="location.href=this.value;">';
		foreach ($albums as $album) {
			$dropdown .= '<option value="' . $album->getUrl() . '"';
			if ($album->path === $subdir . $albumnaam . '/') {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . $album->dirname . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

}

class FotoAlbumToevoegenForm extends ModalForm {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, '/fotoalbum/toevoegen/' . $album->subdir);
		$this->titel = 'Fotoalbum toevoegen in: ' . $album->dirname;
		$this->css_classes[] = 'redirect';
		$fields[] = new RequiredFileNameField('subalbum', null, 'Naam');
		$fields[] = new FormDefaultKnoppen('/fotoalbum', false);
		$this->addFields($fields);
	}

}

class FotoTagToevoegenForm extends InlineForm {

	public function __construct(Foto $foto) {
		$field = new LidField('uid', null, null, LidInstellingenModel::get('fotoalbum', 'tag_suggestions'));
		$field->placeholder = 'Naam of lidnummer';
		parent::__construct(null, '/fotoalbum/addtag/' . $foto->subdir, $field, false, false);
		$fields[] = new RequiredTextField('foto', $foto->filename, null);
		$fields[] = new RequiredIntField('x', null, null, 1, 99);
		$fields[] = new RequiredIntField('y', null, null, 1, 99);
		$fields[] = new RequiredIntField('size', null, null, 1, 99);
		foreach ($fields as $field) {
			$field->hidden = true;
		}
		$this->addFields($fields);
	}

}

class PosterUploadForm extends Formulier {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, '/fotoalbum/uploaden/' . $album->subdir);
		$this->titel = 'Poster toevoegen in: ' . $album->getParentName();
		$fields[] = new HtmlComment('Alleen jpeg afbeeldingen.<br/><br/>');
		$fields[] = new RequiredFileNameField('posternaam', null, 'Posternaam', 50, 5);
		$fields[] = new RequiredImageField('afbeelding', 'Poster', null, null, array('image/jpeg'));
		$fields[] = new FormDefaultKnoppen('/fotoalbum', false);
		$fields[] = new HtmlComment('<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>');
		$this->addFields($fields);
	}

	public function getBreadcrumbs() {
		$view = new FotoAlbumView($this->model);
		return $view->getBreadcrumbs(false, true);
	}

	public function view($showMelding = true) {
		parent::view($showMelding);
		// Uitleg foto's toevoegen
						$body = new CmsPaginaView(CmsPaginaModel::get('fotostoevoegen'));
		$body->view();
	}

}

class FotosDropzone extends Dropzone {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, '/fotoalbum/uploaden/' . $album->subdir, new ImageField('afbeelding', 'Foto', null, null, array('image/jpeg')), '/fotoalbum');
		$this->titel = 'Fotos toevoegen aan: ' . ucfirst($album->dirname);
	}

	public function getBreadcrumbs() {
		$view = new FotoAlbumView($this->model);
		return $view->getBreadcrumbs(false, true);
	}

	public function view($showMelding = true) {
		echo parent::view($showMelding);
		echo '<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>';
		echo '<div class="float-right"><a class="btn" onclick="showExisting_' . $this->formId . '();$(this).remove();">'.Icon::getTag('photos').' Toon bestaande foto\'s in dit album</a></div>';
		// Uitleg foto's toevoegen
						$body = new CmsPaginaView(CmsPaginaModel::get('fotostoevoegen'));
		$body->view();
	}

}
