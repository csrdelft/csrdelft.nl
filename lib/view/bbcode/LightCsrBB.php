<?php

namespace CsrDelft\view\bbcode;
use CsrDelft\model\bibliotheek\BiebBoek;
use CsrDelft\model\documenten\Document;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use function CsrDelft\startsWith;
use function CsrDelft\url_like;
use CsrDelft\view\bibliotheek\BoekBBView;
use Exception;

/**
 * Class LightCsrBB.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class LightCsrBB extends CsrBB
{
	public static function parseLight($bbcode) {
		$parser = new LightCsrBB();
		$parser->light_mode = true;
		return $parser->getHtml($bbcode);
	}

	private function lightLinkBlock($tag, $url, $titel, $beschrijving, $thumbnail = '') {
		$titel = htmlspecialchars($titel);
		$beschrijving = htmlspecialchars($beschrijving);
		if ($thumbnail !== '') {
			$thumbnail = '<img src="' . $thumbnail . '" />';
		}
		return <<<HTML
			<a class="bb-link-block bb-tag-{$tag}" href="{$url}">
				{$thumbnail}
				<h2>{$titel}</h2>
				<p>{$beschrijving}</p>
			</a>
HTML;
	}

	/**
	 * Image
	 *
	 * @param optional String $arguments['class'] Class attribute
	 * @param optional String $arguments['float'] CSS float left or right
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 * @param optional Integer $arguments['h'] CSS height in pixels
	 *
	 * @example [img class=special float=left w=20 h=50]URL[/img]
	 */
	function bb_img($arguments = array()) {
		$url = $this->parseArray(array('[/img]', '[/IMG]'), array());
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (!$url OR ( !url_like($url) AND ! startsWith($url, '/plaetjes/') )) {
			return $url;
		}
		if ($this->light_mode) {
			return '<a class="bb-link-image bb-tag-img" href="' . $url . '"></a>';
		}
	}

	/**
	 * Toont de thumbnail van een foto met link naar fotoalbum.
	 *
	 * @param optional Boolean $arguments['responsive'] Responsive sizing
	 *
	 * @example [foto responsive]/pad/naar/foto[/foto]
	 */
	function bb_foto($arguments = array()) {
		$url = urldecode($this->parseArray(array('[/foto]'), array()));
		$parts = explode('/', $url);
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOS_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		$foto = new Foto($filename, $album);
		if (!$foto) {
			return '';
		}
		$link = $foto->getAlbumUrl() . '#' . $foto->getResizedUrl();
		$thumb = CSR_ROOT . $foto->getThumbUrl();
		return '<a class="bb-link-thumbnail bb-tag-foto" href="' . $link . '"><img src="' . $thumb . '" /></a>';
	}

	/**
	 * Geeft titel en auteur van een boek.
	 * Een kleine indicator geeft met kleuren beschikbaarheid aan
	 *
	 * @example [boek]123[/boek]
	 * @example [boek=123]
	 */
	protected function bb_boek($arguments = array()) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->parseArray(array('[/boek]'), array());
		}

		try {
			$boek = new BiebBoek((int)$boekid);return $this->lightLinkBlock('boek', $boek->getUrl(), $boek->getTitel(), 'Auteur: ' . $boek->getAuteur());
		} catch (Exception $e) {
			return '[boek] Boek [boekid:' . (int)$boekid . '] bestaat niet.';
		}
	}

	/**
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 *
	 * @example [document]1234[/document]
	 * @example [document=1234]
	 */
	protected function bb_document($arguments = array()) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->parseArray(array('[/document]'), array());
		}
		try {
			$document = new Document((int)$id);
			$beschrijving = $document->getFriendlyMimetype() . ' (' . format_filesize((int)$document->getFileSize()) . ')';
			return $this->lightLinkBlock('document', $document->getDownloadUrl(), $document->getNaam(), $beschrijving);
		} catch (Exception $e) {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
		}
	}
}
