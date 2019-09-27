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

	public static function getTagName() {
		return 'peiling';
	}

	public function renderLight() {
		$peiling = $this->getPeiling($this->content);

		$url = '#/peiling/' . urlencode($this->content);
		return BbHelper::lightLinkBlock('peiling', $url, $peiling->titel, $peiling->beschrijving);
	}

	public function render() {
		$peiling = $this->getPeiling($this->content);
		return view('peilingen.peiling', [
			'peiling' => $peiling,
			'opties' => PeilingenLogic::instance()->getOptionsAsJson($peiling->id, LoginModel::getUid()),
		])->getHtml();
	}

	/**
	 * @param string|null $peiling_id
	 * @return Peiling
	 * @throws BbException
	 */
	private function getPeiling($peiling_id): Peiling {
		if (!LoginModel::mag(P_LOGGED_IN)) {
			throw new BbException('');
		}
		$peiling = PeilingenModel::instance()->getPeilingById($peiling_id);
		if ($peiling === false) {
			throw new BbException('[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')');
		}

		return $peiling;
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
