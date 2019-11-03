<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\view\bbcode\BbHelper;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBijbel extends BbTag {

	private $bijbel;
	private $vertaling;
	public static function getTagName() {
		return 'bijbel';
	}

	public function renderLight() {
		list($stukje, $link) = $this->getLink();
		return BbHelper::lightLinkInline($this->env, 'bijbel', $link, $stukje);
	}

	public function render() {
		list($stukje, $link) = $this->getLink();
		return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
	}

	/**
	 * @param $arguments
	 * @return array
	 */
	private function getLink(): array {
		$content = $this->content;
		if ($this->bijbel != null) { // [bijbel=
			$stukje = str_replace('_', ' ', $this->bijbel);
		} else { // [bijbel][/bijbel]
			$stukje = $content;
		}

		$vertaling1 = $this->vertaling;
		if (!LidInstellingenModel::instance()->isValidValue('algemeen', 'bijbel', $vertaling1)) {
			$vertaling1 = null;
		}
		if ($vertaling1 === null) {
			$vertaling1 = lid_instelling('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . urlencode($vertaling1) . '/' . urlencode($stukje);
		return array($stukje, $link);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
		$this->bijbel = $arguments['bijbel'] ?? null;
		$this->vertaling = $arguments['vertaling'] ?? null;
	}
}
