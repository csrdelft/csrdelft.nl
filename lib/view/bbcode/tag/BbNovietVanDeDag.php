<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class BbNovietVanDeDag extends BbTag
{
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Security $security,
		ProfielRepository $profielRepository,
		Environment $twig
	) {
		$this->profielRepository = $profielRepository;
		$this->twig = $twig;
		$this->security = $security;
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

			uksort($profielen, function ($a, $b) use ($volgorde) {
				return $volgorde[$a] <=> $volgorde[$b];
			});

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
