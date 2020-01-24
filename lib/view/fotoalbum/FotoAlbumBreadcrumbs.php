<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\common\CsrNotFoundException;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 31/12/2018
 */
class FotoAlbumBreadcrumbs {
	public static function getBreadcrumbs(FotoAlbum $album, $dropdown = true, $self = false) {
		return static::getBreadcrumbsDropdown($album, $dropdown, $self);
	}

	private static function getBreadcrumbsDropdown(FotoAlbum $album, $dropdown, $self) {
		$breadcrumbs = '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>';

		if ($album->subdir == 'fotoalbum/') {
			// Geen subdir
			$breadcrumbs .= '<li class="breadcrumb-item active">Fotoalbum</li>';
		} else {
			$breadcrumbs .= '<li class="breadcrumb-item"><a href="/fotoalbum">Fotoalbum</a></li>';
		}
		$mappen = explode('/', $album->subdir);
		$subdir = '';
		foreach ($mappen as $albumnaam) {
			if ($albumnaam === '') {
				// trailing slash: allerlaatste
				break;
			} else {
				if ($albumnaam === $album->dirname) {
					// laatste
					if ($dropdown) {
						$breadcrumbs .= static::getDropDown($subdir, $albumnaam);
						break;
					} elseif (!$self) {
						// alleen parent folders tonen
						break;
					}
				}
				$subdir = join_paths($subdir, $albumnaam);
				$breadcrumbs .= '<li class="breadcrumb-item"><a href="/fotoalbum/' . $subdir . '">' . ucfirst($albumnaam) . '</a></li>';
			}
		}
		return $breadcrumbs;
	}

	private static function getDropDown($subdir, $albumnaam) {
		try {
			$parent = FotoAlbumModel::instance()->getFotoAlbum($subdir);
			$albums = $parent->getSubAlbums();
			$dropdown = '<select onchange="location.href=this.value;">';
			foreach ($albums as $album) {
				$dropdown .= '<option value="' . $album->getUrl() . '"';
				if ($album->subdir === join_paths($subdir, $albumnaam)) {
					$dropdown .= ' selected="selected"';
				}
				$dropdown .= '>' . $album->dirname . '</option>';
			}
			$dropdown .= '</select>';
			return '<li class="breadcrumb-item">' . $dropdown . '</li>';
		} catch (CsrNotFoundException $ex) {
			return '';
		}
	}
}
