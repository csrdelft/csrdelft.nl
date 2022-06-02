<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\Icon;
use function trim;
use function urlencode;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbLocatie extends BbTag
{

	/**
	 * @var int
	 */
	private $height;

	public static function getTagName()
	{
		return ['map', 'kaart', 'locatie'];
	}

	public function renderLight()
	{
		$address = $this->getContent();
		$url = 'https://maps.google.nl/maps?q=' . urlencode($address);
		return BbHelper::lightLinkInline($this->env, 'locatie', $url, $address);
	}

	public function render()
	{
		$address = $this->getContent();
		$url = 'https://maps.google.nl/maps?q=' . urlencode($address);
		if (trim(htmlspecialchars($address)) == '') {
			$maps = 'Geen adres opgegeven';
		} else {
			$maps = '<iframe height="' . $this->height . '" frameborder="0" style="border:0;width:100%" src="https://www.google.com/maps/embed/v1/place?q=' . urlencode(htmlspecialchars($address)) . '&key=' . $_ENV['GOOGLE_EMBED_KEY'] . '"></iframe>';
		}
		$map = $maps;
		return '<span class="hoverIntent"><a href="' . $url . '">' . $address . Icon::getTag('map', null, 'Kaart', 'text') . '</a><span class="hoverIntentContent">' . $map . '</span></span>';
	}

	public function parse($arguments = [])
	{
		// Hoogte maakt niet veel uit
		if (isset($arguments['h']) && $arguments['h'] <= 900) {
			$this->height = (int)$arguments['h'];
		} else {
			$this->height = 450;
		}
		$this->readContent([], false);
	}

}
