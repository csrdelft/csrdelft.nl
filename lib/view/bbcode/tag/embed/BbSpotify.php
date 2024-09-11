<?php

namespace CsrDelft\view\bbcode\tag\embed;

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
class BbSpotify extends BbTag
{
	public $formaat;
	/**
	 * @var string
	 */
	public $uri;

	public static function getTagName()
	{
		return 'spotify';
	}

	public function renderLight()
	{
		$url =
			'https://open.spotify.com/' .
			str_replace(':', '/', str_replace('spotify:', '', $this->uri));
		return BbHelper::lightLinkBlock(
			'spotify',
			$url,
			'Spotify',
			$this->getBeschrijving()
		);
	}

	public function render()
	{
		$commonAttributen = "src=\"https://embed.spotify.com/?uri=$this->uri\" frameborder=\"0\" allowtransparency=\"true\"";

		return match ($this->formaat) {
			'hoog'
				=> "<iframe class=\"w-100\" height=\"380\" $commonAttributen></iframe>",
			'blok'
				=> "<iframe width=\"80\" height=\"80\" class=\"float-start\" $commonAttributen></iframe>",
			default
				=> "<iframe class=\"w-100\" height=\"80\" $commonAttributen></iframe>",
		};
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->formaat = $arguments['formaat'] ?? null;
		$url = $this->readMainArgument($arguments);
		if (
			!str_starts_with($url, 'spotify') &&
			!filter_var($url, FILTER_VALIDATE_URL)
		) {
			throw new BbException('[spotify] Geen geldige url (' . $url . ')');
		}
		$this->uri = urlencode($url);
	}

	private function getBeschrijving()
	{
		if (strstr($this->uri, 'playlist')) {
			return 'Afspeellijst';
		} elseif (strstr($this->uri, 'album')) {
			return 'Album';
		} elseif (strstr($this->uri, 'track')) {
			return 'Nummer';
		} else {
			return '';
		}
	}
}
