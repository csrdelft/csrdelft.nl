<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\view\bbcode\CsrBbException;
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
		$foto = $this->getFoto(explode('/', $url), $url);
		return $this->lightLinkThumbnail('foto', $foto->getAlbumUrl() . '#' . $foto->getResizedUrl(), CSR_ROOT . $foto->getThumbUrl());
	}

	public function parse($arguments = []) {
		$url = urldecode($this->getContent());
		$parts = explode('/', $url);
		$fototag = new FotoBBView($this->getFoto($parts, $url), in_array('Posters', $parts), isset($arguments['responsive']));
		return $fototag->getHtml();
	}

	private function getFoto(array $parts, string $url): Foto {
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOALBUM_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			throw new CsrBbException('<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>');
		}
		$foto = new Foto($filename, $album);
		if (!$foto) {
			throw new CsrBbException('');
		}
		return $foto;
	}
}
