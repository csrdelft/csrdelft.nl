<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;

/**
 * Laat de embedded spotify player zien
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [spotify]https://open.spotify.com/user/.../playlist/...[/spotify]
 * @example [spotify]spotify:user:...:playlist:...[/spotify]
 */
class BbSpotify extends BbTag {

	public function getTagName() {
		return 'spotify';
	}

	public function parseLight($arguments = []) {
		$uri = $this->getArgument($arguments);
		$this->assertUri($uri);

		$url = 'https://open.spotify.com/' . str_replace(':', '/', str_replace('spotify:', '', $uri));
		if (strstr($uri, 'playlist')) {
			$beschrijving = 'Afspeellijst';
		} elseif (strstr($uri, 'album')) {
			$beschrijving = 'Album';
		} elseif (strstr($uri, 'track')) {
			$beschrijving = 'Nummer';
		} else {
			$beschrijving = '';
		}
		return BbHelper::lightLinkBlock('spotify', $url, 'Spotify', $beschrijving);
	}

	public function parse($arguments = []) {
		$uri = $this->getArgument($arguments);
		$this->assertUri($uri);

		$commonAttributen = "src=\"https://embed.spotify.com/?uri=$uri\" frameborder=\"0\" allowtransparency=\"true\"";

		if (isset($arguments['formaat'])) {
			$formaat = $arguments['formaat'];
			if ($formaat == "hoog") {
				return "<iframe width=\"300\" height=\"380\" $commonAttributen></iframe>";
			} elseif ($formaat == "blok") {
				return "<iframe width=\"80\" height=\"80\" class=\"float-left\" $commonAttributen></iframe>";
			}
		}

		return "<iframe class=\"w-100\" height=\"80\" $commonAttributen></iframe>";
	}

	/**
	 * @param string|null $uri
	 * @throws BbException
	 */
	private function assertUri($uri) {
		if (!startsWith($uri, 'spotify') && !filter_var($uri, FILTER_VALIDATE_URL)) {
			throw new BbException('[spotify] Geen geldige url (' . $uri . ')');
		}
	}
}
