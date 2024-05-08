<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\view\Icon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 31/12/2018
 */
class FotoAlbumBreadcrumbs
{
	public static function getBreadcrumbs(
		FotoAlbum $album,
		$dropdown = true,
		$self = false
	) {
		return static::getBreadcrumbsDropdown($album, $dropdown, $self);
	}

	private static function getBreadcrumbsDropdown(
		FotoAlbum $album,
		$dropdown,
		$self
	): string {
		$breadcrumbs =
			'<li class="breadcrumb-item"><a href="/">' .
			Icon::getTag('home') .
			'</a></li>';

		if ($album->subdir == 'fotoalbum/') {
			// Geen subdir
			$breadcrumbs .= '<li class="breadcrumb-item active">Fotoalbum</li>';
		} else {
			$breadcrumbs .=
				'<li class="breadcrumb-item"><a href="/fotoalbum">Fotoalbum</a></li>';
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
				$subdir = PathUtil::join_paths($subdir, $albumnaam);
				$breadcrumbs .=
					'<li class="breadcrumb-item"><a href="/fotoalbum/' .
					$subdir .
					'">' .
					ucfirst($albumnaam) .
					'</a></li>';
			}
		}
		return $breadcrumbs;
	}

	private static function getDropDown($subdir, $albumnaam)
	{
		try {
			$parent = ContainerFacade::getContainer()
				->get(FotoAlbumRepository::class)
				->getFotoAlbum($subdir);
			$albums = $parent->getSubAlbums();
			$dropdown = '<select onchange="location.href=this.value;">';
			foreach ($albums as $album) {
				$dropdown .= '<option value="' . $album->getUrl() . '"';
				if ($album->subdir === PathUtil::join_paths($subdir, $albumnaam)) {
					$dropdown .= ' selected="selected"';
				}
				$dropdown .= '>' . $album->dirname . '</option>';
			}
			$dropdown .= '</select>';
			return '<li class="breadcrumb-item">' . $dropdown . '</li>';
		} catch (NotFoundHttpException $ex) {
			return '';
		}
	}
}
