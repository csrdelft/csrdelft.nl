<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\bibliotheek\BoekBBView;

/**
 * Geeft titel en auteur van een boek.
 * Een kleine indicator geeft met kleuren beschikbaarheid aan
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [boek]123[/boek]
 * @example [boek=123]
 */
class BbBoek extends BbTag {

	public static function getTagName() {
		return 'boek';
	}
	public function isAllowed()
	{
		LoginModel::mag(P_BIEB_READ);
	}

	public function renderLight() {
		try {
			/** @var Boek $boek */
			$boek = BoekModel::instance()->get((int)$this->content);
			return BbHelper::lightLinkBlock('boek', $boek->getUrl(), $boek->getTitel(), 'Auteur: ' . $boek->getAuteur());
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$this->content . '] bestaat niet.';
		}
	}

	public function render() {
		if (!mag("P_BIEB_READ")) return null;

		try {
			/** @var Boek $boek */
			$boek = BoekModel::instance()->get((int)$this->content);
			$content = new BoekBBView($boek);
			return $content->view();
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$this->content . '] bestaat niet.';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
