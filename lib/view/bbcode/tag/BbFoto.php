<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrNotFoundException;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\view\bbcode\BbHelper;
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

	/**
	 * @var bool
	 */
	private $responsive;
	/**
	 * @var Foto
	 */
	private $foto;
	public function isAllowed()
	{
		return $this->foto->magBekijken();
	}

	public static function getTagName() {
		return 'foto';
	}

	public function renderLight() {
		return BbHelper::lightLinkThumbnail('foto', $this->foto->getAlbumUrl() . '#' . $this->foto->getResizedUrl(), CSR_ROOT . $this->foto->getThumbUrl());
	}

	public function render() {
		$url = $this->content;
		$parts = explode('/', $url);
		$fototag = new FotoBBView($this->foto, in_array('Posters', $parts), $this->responsive);
		return $fototag->getHtml();
	}

	/**
	 * @param array $parts
	 * @param string $url
	 * @return Foto
	 * @throws BbException
	 */
	private function getFoto(array $parts, string $url): Foto {
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = implode('/', $parts);
		$path = str_replace('fotoalbum/', '', $path);
		try {
			$album = FotoAlbumModel::instance()->getFotoAlbum($path);
			$foto = new Foto($filename, $album);
			if (!$foto->exists()) {
				throw new BbException('Foto niet gevonden.');
			}
			return $foto;
		} catch (CsrNotFoundException $ex) {
			throw new BbException('<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>');
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->responsive = isset($arguments['responsive']);
		$this->readMainArgument($arguments);
		$this->foto =  $this->getFoto(explode('/', $this->content), $this->content);
	}
}
