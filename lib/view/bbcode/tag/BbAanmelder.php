<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\repository\aanmelder\AanmeldActiviteitRepository;
use CsrDelft\repository\aanmelder\ReeksRepository;
use Twig\Environment;

class BbAanmelder extends BbTag
{
	private $reeks;
	private $activiteit;
	private $aantal;

	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;
	/**
	 * @var AanmeldActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(
		ReeksRepository $reeksRepository,
		AanmeldActiviteitRepository $activiteitRepository,
		Environment $twig
	) {
		$this->reeksRepository = $reeksRepository;
		$this->activiteitRepository = $activiteitRepository;
		$this->twig = $twig;
	}

	public static function getTagName()
	{
		return 'aanmelder';
	}

	public function parse($arguments = [])
	{
		if (isset($arguments['aanmelder'])) {
			$this->activiteit = intval($arguments['aanmelder']);
		} elseif (isset($arguments['reeks'])) {
			$this->reeks = intval($arguments['reeks']);
			$this->aantal = max(intval($arguments['aantal'] ?? 100), 1);
		}
	}

	public function render()
	{
		if (isset($this->reeks)) {
			$reeks = $this->reeksRepository->find($this->reeks);
			if (!$reeks) {
				return "Reeks met id {$this->reeks} niet gevonden.";
			}

			$activiteiten = $this->activiteitRepository->getKomendeActiviteiten(
				$reeks
			);
			$toonMeer = count($activiteiten) > $this->aantal;
			$activiteiten = $activiteiten->slice(0, $this->aantal);
		} else {
			/** @var AanmeldActiviteit $activiteit */
			$activiteit = $this->activiteitRepository->find($this->activiteit);
			if (!$activiteit) {
				return "Activiteit met id {$this->activiteit} niet gevonden.";
			}
			$activiteiten = [$activiteit];
			$toonMeer = false;
			$reeks = $activiteit->getReeks();
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->twig->render('aanmelder/bb_lijst.html.twig', [
			'activiteiten' => $activiteiten,
			'reeks' => $reeks,
			'toonMeer' => $toonMeer,
		]);
	}
}
