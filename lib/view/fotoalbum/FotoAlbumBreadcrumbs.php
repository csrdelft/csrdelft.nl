<?php

namespace CsrDelft\view\fotoalbum;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 31/12/2018
 */
class FotoAlbumBreadcrumbs {
	public static function getBreadcrumbs(FotoAlbum $album, $dropdown = true, $self = false) {
		return static::getBreadcrumbsDropdown($album, $dropdown, $self);
	}

	private static function getBreadcrumbsDropdown(FotoAlbum $album, $dropdown, $self) {
		$breadcrumbs = '<a href="/fotoalbum" title="Fotoalbum"><span class="fa fa-camera module-icon"></span></a>';
		$mappen = explode('/', $album->subdir);
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
				if ($albumnaam === $album->dirname) {
					// laatste
					if ($dropdown) {
						$breadcrumbs .= ' » ' . static::getDropDown(PHOTOALBUM_PATH . $subdir, $albumnaam);
						break;
					} elseif (!$self) {
						// alleen parent folders tonen
						break;
					}
				}
				$subdir .= $albumnaam . '/';
				$breadcrumbs .= '   <a href="/' . $subdir . '">' . ucfirst($albumnaam) . '</a>';
			}
		}
		return $breadcrumbs;
	}

	private static function getDropDown($subdir, $albumnaam) {
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
