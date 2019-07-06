<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\BbHelper;

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
		$peiling = $this->getPeiling($peiling_id);

		$url = '#/peiling/' . urlencode($peiling_id);
		return BbHelper::lightLinkBlock('peiling', $url, $peiling->titel, $peiling->beschrijving);
	}

	public function parse($arguments = []) {
		$peiling_id = $this->getArgument($arguments);
		$peiling = $this->getPeiling($peiling_id);
		return view('peilingen.peiling', [
			'peiling' => $peiling,
			'opties' => PeilingenLogic::instance()->getOptionsAsJson($peiling_id, LoginModel::getUid()),
		])->getHtml();
	}

	/**
	 * @param string|null $peiling_id
	 * @return Peiling
	 * @throws BbException
	 */
	private function getPeiling($peiling_id): Peiling {
		$peiling = PeilingenModel::instance()->getPeilingById($peiling_id);
		if ($peiling === false) {
			throw new BbException('[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')');
		}

		return $peiling;
	}
}
