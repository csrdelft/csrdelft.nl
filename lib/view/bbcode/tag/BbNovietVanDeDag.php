<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class BbNovietVanDeDag extends BbTag
{
	public function __construct(
		private readonly Security $security,
		private readonly ProfielRepository $profielRepository,
		private readonly Environment $twig
	) {
	}

	public static function getTagName()
	{
		return 'novietvandedag';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public function parse($arguments = [])
	{
		// geen argumenten
	}

	public function render()
	{
		// Haal profielen van novieten op
		$profielen = $this->profielRepository->findByLidStatus([LidStatus::Noviet]);
		$aantal = count($profielen);

		if ($aantal > 0) {
			// Selecteer noviet van deze dag
			$dagenSindsStart = intval(
				date_create_immutable('2020-12-15')
					->diff(date_create_immutable('midnight'))
					->format('%a')
			);
			$run = floor($dagenSindsStart / $aantal);
			$positie = $dagenSindsStart % $aantal;

			@mt_srand(181818 + $run);
			$volgorde = [];
			for ($i = 0; $i < $aantal; $i++) {
				$volgorde[] = @mt_rand();
			}

			uksort($profielen, fn($a, $b) => $volgorde[$a] <=> $volgorde[$b]);

			$noviet = array_values($profielen)[$positie];

			// Render
			/** @noinspection PhpUnhandledExceptionInspection */
			return $this->twig->render('profiel/noviet_van_de_dag.html.twig', [
				'noviet' => $noviet,
			]);
		} else {
			return '';
		}
	}

	public function renderLight()
	{
		// Niet light te renderen
	}
}
