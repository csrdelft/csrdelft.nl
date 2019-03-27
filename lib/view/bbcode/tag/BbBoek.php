<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\common\CsrException;
use CsrDelft\model\bibliotheek\BoekModel;
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

	public function getTagName() {
		return 'boek';
	}

	public function parseLight($arguments = []) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->getContent();
		}

		try {
			$boek = BoekModel::instance()->get((int)$boekid);
			return $this->lightLinkBlock('boek', $boek->getUrl(), $boek->getTitel(), 'Auteur: ' . $boek->getAuteur());
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$boekid . '] bestaat niet.';
		}
	}

	public function parse($arguments = []) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->getContent();
		}

		try {
			$boek = BoekModel::instance()->get((int)$boekid);
			$content = new BoekBBView($boek);
			return $content->view();
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$boekid . '] bestaat niet.';
		}
	}
}
