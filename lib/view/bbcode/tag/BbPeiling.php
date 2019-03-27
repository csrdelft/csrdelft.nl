<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\security\LoginModel;

/**
 * Peiling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [peiling=2]
 * @example [peiling]2[/peiling]
 */
class BbPeiling extends BbTag {

	public function getTagName() {
		return 'peiling';
	}

	public function parseLight($arguments = []) {
		$peiling_id = $this->getArgument($arguments);
		$peiling = PeilingenModel::instance()->getPeilingById((int)$peiling_id);
		if ($peiling === false) {
			return '[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')';
		}

		$url = '#/peiling/' . urlencode($peiling_id);
		return $this->lightLinkBlock('peiling', $url, $peiling->titel, $peiling->beschrijving);
	}

	public function parse($arguments = []) {
		$peiling_id = $this->getArgument($arguments);
		$peiling = PeilingenModel::instance()->getPeilingById($peiling_id);
		if ($peiling === false) {
			return '[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')';
		}
		return view('peilingen.peiling', [
			'peiling' => $peiling,
			'opties' => PeilingenLogic::instance()->getOptionsAsJson($peiling_id, LoginModel::getUid()),
		])->getHtml();
	}
}
