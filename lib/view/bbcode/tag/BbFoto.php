<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\view\fotoalbum\FotoBBView;

/**
 * Toont de thumbnail van een foto met link naar fotoalbum.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param optional Boolean $arguments['responsive'] Responsive sizing
 *
 * @example [foto responsive]/pad/naar/foto[/foto]
 */
class BbFoto extends BbTag {

	public function getTagName() {
		return 'foto';
	}

	public function parseLight($arguments = []) {
		$url = urldecode($this->getContent());
		$parts = explode('/', $url);
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOALBUM_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		$foto = new \CsrDelft\model\entity\fotoalbum\Foto($filename, $album);
		if (!$foto) {
			return '';
		}
		$link = $foto->getAlbumUrl() . '#' . $foto->getResizedUrl();
		$thumb = CSR_ROOT . $foto->getThumbUrl();
		return $this->lightLinkThumbnail('foto', $link, $thumb);
	}

	public function parse($arguments = []) {
		$url = urldecode($this->getContent());
		$parts = explode('/', $url);
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOALBUM_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		$foto = new \CsrDelft\model\entity\fotoalbum\Foto($filename, $album);
		if (!$foto) {
			return '';
		}
		$groot = in_array('Posters', $parts);
		$responsive = isset($arguments['responsive']);
		$fototag = new FotoBBView($foto, $groot, $responsive);
		return $fototag->getHtml();
	}
}
