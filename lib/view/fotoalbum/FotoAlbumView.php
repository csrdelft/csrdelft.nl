<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * FotoAlbumView.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De views van het fotoalbum.
 *
 * @property FotoAlbum $model
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
		$this->smarty->assign('itemsJson', json_encode($this->model->getAlbumArrayRecursive()));
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
