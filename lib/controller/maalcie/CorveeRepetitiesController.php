<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\view\maalcie\forms\CorveeRepetitieForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CorveeRepetitiesController {
	private $repetitie = null;
	/**
	 * @var CorveeRepetitiesRepository
	 */
	private $corveeRepetitiesRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;

	public function __construct(CorveeRepetitiesRepository $corveeRepetitiesRepository, MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository, CorveeTakenRepository $corveeTakenRepository, CorveeVoorkeurenRepository $corveeVoorkeurenRepository) {
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
	}

	public function beheer($crid = null, $mrid = null) {
		$modal = null;
		$maaltijdrepetitie = null;
		if (is_numeric($crid) && $crid > 0) {
			$modal = $this->bewerk($crid);
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		} elseif (is_numeric($mrid) && $mrid > 0) {
			$repetities = $this->corveeRepetitiesRepository->getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = $this->maaltijdRepetitiesRepository->getRepetitie($mrid);
		} else {
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		}
		return view('maaltijden.corveerepetitie.beheer_corvee_repetities', [
			'repetities' => $repetities,
			'maaltijdrepetitie' => $maaltijdrepetitie,
			'modal' => $modal,
		]);
	}

	public function maaltijd($mrid) {
		return $this->beheer(null, $mrid);
	}

	public function nieuw($mrid = null) {
		$repetitie = $this->corveeRepetitiesRepository->nieuw($mrid);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	public function bewerk($crid) {
		$repetitie = $this->corveeRepetitiesRepository->getRepetitie($crid);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	/**
	 * @param EntityManagerInterface $em
	 * @param $crid
	 * @return CorveeRepetitieForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function opslaan(EntityManagerInterface $em, $crid) {
		if ($crid > 0) {
			$view = $this->bewerk($crid);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			/** @var CorveeRepetitie $repetitie */
			$repetitie = $view->getModel();

			$em->persist($repetitie);
			$em->flush();

			if (!$repetitie->voorkeurbaar) { // niet (meer) voorkeurbaar
				$aantal = $this->corveeVoorkeurenRepository->verwijderVoorkeuren($crid);

				if ($aantal > 0) {
					setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
				}
			}

			return view('maaltijden.corveerepetitie.beheer_corvee_repetitie', ['repetitie' => $repetitie]);
		}

		return $view;
	}

	public function verwijder($crid) {
		$aantal = $this->corveeRepetitiesRepository->verwijderRepetitie($crid);
		if ($aantal > 0) {
			setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $crid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken(EntityManagerInterface $em, $crid) {
		$view = $this->opslaan($em, $crid);
		if ($this->repetitie) { // Opslaan gelukt
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = $this->corveeTakenRepository->updateRepetitieTaken($this->repetitie, $verplaats);
			if ($aantal['update'] < $aantal['day']) {
				$aantal['update'] = $aantal['day'];
			}
			setMelding(
				$aantal['update'] . ' corveeta' . ($aantal['update'] !== 1 ? 'ken' : 'ak') . ' bijgewerkt waarvan ' .
				$aantal['day'] . ' van dag verschoven.', 1);
			$aantal['datum'] += $aantal['maaltijd'];
			setMelding(
				$aantal['datum'] . ' corveeta' . ($aantal['datum'] !== 1 ? 'ken' : 'ak') . ' aangemaakt waarvan ' .
				$aantal['maaltijd'] . ' maaltijdcorvee.', 1);
		}

		return $view;
	}
}
