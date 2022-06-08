<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use Twig\Environment;

/**
 * Geeft titel en auteur van een boek.
 * Een kleine indicator geeft met kleuren beschikbaarheid aan
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [boek]123[/boek]
 * @example [boek=123]
 */
class BbBoek extends BbTag
{
	/**
	 * @var BoekRepository
	 */
	private $boekRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(BoekRepository $boekRepository, Environment $twig)
	{
		$this->boekRepository = $boekRepository;
		$this->twig = $twig;
	}

	public static function getTagName()
	{
		return 'boek';
	}
	public function isAllowed()
	{
		return LoginService::mag(P_BIEB_READ);
	}

	public function renderLight()
	{
		try {
			$boek = $this->boekRepository->find($this->id);
			return BbHelper::lightLinkBlock(
				'boek',
				$boek->getUrl(),
				$boek->titel,
				'Auteur: ' . $boek->auteur
			);
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int) $this->id . '] bestaat niet.';
		}
	}

	public function render()
	{
		if (!LoginService::mag('ROLE_BIEB_READ')) {
			return null;
		}

		try {
			$boek = $this->boekRepository->find($this->id);
			return $this->twig->render('bibliotheek/boek-bb.html.twig', [
				'boek' => $boek,
			]);
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int) $this->id . '] bestaat niet.';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
	}
}
