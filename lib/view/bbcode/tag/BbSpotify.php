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

	private $formaat;

	public static function getTagName() {
		return 'spotify';
	}

	public function renderLight() {
		$url = 'https://open.spotify.com/' . str_replace(':', '/', str_replace('spotify:', '', $this->content));
		return BbHelper::lightLinkBlock('spotify', $url, 'Spotify', $this->getBeschrijving());
	}

	public function render() {
		$commonAttributen = "src=\"https://embed.spotify.com/?uri=$this->>content\" frameborder=\"0\" allowtransparency=\"true\"";

		switch($this->formaat) {
			case "hoog":
				return "<iframe class=\"w-100\" height=\"380\" $commonAttributen></iframe>";
			case "blok":
				return "<iframe width=\"80\" height=\"80\" class=\"float-left\" $commonAttributen></iframe>";
			default:
				return "<iframe class=\"w-100\" height=\"80\" $commonAttributen></iframe>";
		}
	}


	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->formaat = $arguments['formaat'] ?? null;
		$this->readMainArgument($arguments);
		if (!startsWith($this->content, 'spotify') && !filter_var($this->content, FILTER_VALIDATE_URL)) {
			throw new BbException('[spotify] Geen geldige url (' . $this->content . ')');
		}
		$this->content = urlencode($this->content);
	}

	private function getBeschrijving()
	{
		$uri = $this->content;
		if (strstr($uri, 'playlist')) {
			return'Afspeellijst';
		} elseif (strstr($uri, 'album')) {
			return 'Album';
		} elseif (strstr($uri, 'track')) {
			return 'Nummer';
		} else {
			return '';
		}
	}
}
