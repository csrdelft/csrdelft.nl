<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\Ini;
use CsrDelft\view\bbcode\BbHelper;
use function trim;
use function urlencode;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbMap extends BbTag {

	public function getTagName() {
		return ['map', 'kaart'];
	}

	public function parseLight($arguments = []) {
		$address = $this->getContent();
		$url = 'https://maps.google.nl/maps?q=' . urlencode($address);
		return BbHelper::lightLinkBlock('map', $url, $address, 'Google Maps');
	}

	public function parse($arguments = []) {
		$address = $this->getContent();
		if (trim(htmlspecialchars($address)) == '') {
			$maps = 'Geen adres opgegeven';
		} else {
			// Hoogte maakt niet veel uit
			if (isset($arguments['h']) && $arguments['h'] <= 900) {
				$height = (int)$arguments['h'];
			} else {
				$height = 450;
			}

			$maps = '<iframe height="' . $height . '" frameborder="0" style="border:0;width:100%" src="https://www.google.com/maps/embed/v1/place?q=' . urlencode(htmlspecialchars($address)) . '&key=' . Ini::lees(Ini::GOOGLE, 'embed_key') . '"></iframe>';
		}
		return $maps;
	}

}
