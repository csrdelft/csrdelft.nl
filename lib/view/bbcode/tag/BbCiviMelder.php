<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\repository\civimelder\ReeksRepository;
use Twig\Environment;

class BbCiviMelder extends BbTag {
	private $reeks;
	private $activiteit;
	private $aantal;

	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;
	/**
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(ReeksRepository $reeksRepository,
															ActiviteitRepository $activiteitRepository,
															DeelnemerRepository $deelnemerRepository,
															Environment $twig) {
		$this->reeksRepository = $reeksRepository;
		$this->activiteitRepository = $activiteitRepository;
		$this->deelnemerRepository = $deelnemerRepository;
		$this->twig = $twig;
	}

	public static function getTagName() {
		return 'civimelder';
	}

	public function parse($arguments = []) {
		if (isset($arguments['civimelder'])) {
			$this->activiteit = intval($arguments['civimelder']);
		} elseif (isset($arguments['reeks'])) {
			$this->reeks = intval($arguments['reeks']);
			$this->aantal = min(intval($arguments['aantal'] ?? 3), 1);
		}
	}

	public function render() {
		if (isset($this->reeks)) {
			/** @var Reeks $reeks */
			$reeks = $this->reeksRepository->find($this->reeks);
			if (!$reeks) {
				return "Reeks met id {$this->reeks} niet gevonden.";
			}

			$activiteiten = $this->activiteitRepository->getKomendeActiviteiten($reeks)->slice(0, $this->aantal);
		} else {
			/** @var Activiteit $activiteit */
			$activiteit = $this->activiteitRepository->find($this->activiteit);
			if (!$activiteit) {
				return "Activiteit met id {$this->activiteit} niet gevonden.";
			}
			$activiteiten = [$activiteit];
			$reeks = $activiteit->getReeks();
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->twig->render('civimelder/bb_lijst.html.twig', [
			'activiteiten' => $activiteiten,
			'reeks' => $reeks,
		]);
	}
}
