<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\fotoalbum\FotoTagAlbum;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\fotoalbum\FotoAlbumBBView;

/**
 * Fotoalbum
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * Albumweergave (default):
 * @param boolean optional $arguments['compact'] Compacte weergave
 * @param integer optional $arguments['rows'] Aantal rijen
 * @param integer optional $arguments['perrow'] Aantal kolommen
 * @param boolean optional $arguments['bigfirst'] Eerste foto groot
 * @param string optional $arguments['big'] Indexen van foto's die groot moeten, of patroon 'a', 'b' of 'c'
 *
 * @example [fotoalbum compact bigfirst]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum rows=2 perrow=5 big=a]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum big=0,5,14]/pad/naar/album[/fotoalbum]
 *
 * Sliderweergave:
 * @param boolean optional $arguments['slider'] Slider weergave
 * @param integer optional $arguments['interval'] Slider interval in seconden
 * @param boolean optional $arguments['random'] Slider met random volgorde
 * @param boolean optional $arguments['height'] Slider hoogte in pixels
 *
 * @example [fotoalbum slider interval=10 random height=200]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum]laatste[/fotoalbum]
 */
class BbFotoalbum extends BbTag {

	public function getTagName() {
		return 'fotoalbum';
	}

	public function parseLight($arguments = []) {
		$url = urldecode($this->getContent());
		$album = $this->getAlbum($url);
		$beschrijving = count($album->getFotos()) . ' foto\'s';
		$cover = CSR_ROOT . $album->getCoverUrl();
		return BbHelper::lightLinkBlock('fotoalbum', $album->getUrl(), $album->dirname, $beschrijving, $cover);
	}

	public function parse($arguments = []) {
		$url = urldecode($this->getContent());
		$album = $this->getAlbum($url);
		if (isset($arguments['slider'])) {
			$view = view('fotoalbum.slider', [
				'fotos' => array_shuffle($album->getFotos())
			]);
		} else {
			$view = new FotoAlbumBBView($album);

			if ($this->env->quote_level > 0 || isset($arguments['compact'])) {
				$view->makeCompact();
			}
			if (isset($arguments['rows'])) {
				$view->setRows((int)$arguments['rows']);
			}
			if (isset($arguments['perrow'])) {
				$view->setPerRow((int)$arguments['perrow']);
			}
			if (isset($arguments['bigfirst'])) {
				$view->setBig(0);
			}
			if (isset($arguments['big'])) {
				if ($arguments['big'] == 'first') {
					$view->setBig(0);
				} else {
					$view->setBig($arguments['big']);
				}
			}
		}
		return $view->getHtml();
	}

	/**
	 * @param string $url
	 * @return bool|FotoAlbum|FotoTagAlbum|null
	 * @throws BbException
	 */
	private function getAlbum(string $url) {
		if ($url === 'laatste') {
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
		} else {
			//vervang url met pad
			$url = str_ireplace(CSR_ROOT, '', $url);
			$path = PHOTOALBUM_PATH;
			//check fotoalbum in url
			$url = str_ireplace('fotoalbum/', '', $url);
			$path .= 'fotoalbum/';
			//check slash voor pad
			if (startsWith($url, '/')) {
				$url = substr($url, 1);
			}
			$path .= $url;
			$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		}
		if (!$album) {
			throw new BbException('<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>');
		}
		return $album;
	}
}
