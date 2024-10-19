<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\view\bbcode\BbHelper;
use Symfony\Bundle\SecurityBundle\Security;
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
	 * @var string
	 */
	private $id;

	public function __construct(
		private readonly BoekRepository $boekRepository,
		private readonly Environment $twig,
		private readonly Security $security
	) {
	}

	public static function getTagName()
	{
		return 'boek';
	}
	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_BIEB_READ');
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
		} catch (CsrException) {
			return '[boek] Boek [boekid:' . (int) $this->id . '] bestaat niet.';
		}
	}

	public function render()
	{
		if (!$this->security->isGranted('ROLE_BIEB_READ')) {
			return null;
		}

		try {
			$boek = $this->boekRepository->find($this->id);
			return $this->twig->render('bibliotheek/boek-bb.html.twig', [
				'boek' => $boek,
			]);
		} catch (CsrException) {
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
