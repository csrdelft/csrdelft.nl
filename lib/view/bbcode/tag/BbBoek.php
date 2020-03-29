<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\view\bbcode\BbHelper;

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
	/**
	 * @var BoekRepository
	 */
	private $boekRepository;

	public function __construct(BoekRepository $boekRepository) {
		$this->boekRepository = $boekRepository;
	}

	public static function getTagName() {
		return 'boek';
	}
	public function isAllowed()
	{
		LoginModel::mag(P_BIEB_READ);
	}

	public function renderLight() {
		try {
			$boek = $this->boekRepository->find($this->content);
			return BbHelper::lightLinkBlock('boek', $boek->getUrl(), $boek->titel, 'Auteur: ' . $boek->auteur);
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$this->content . '] bestaat niet.';
		}
	}

	public function render() {
		if (!mag("P_BIEB_READ")) return null;

		try {
			$boek = $this->boekRepository->find($this->content);
			return view('bibliotheek.boek-bb', ['boek' => $boek])->getHtml();
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
