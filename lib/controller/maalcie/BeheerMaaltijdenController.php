<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\ArchiefMaaltijdModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\maalcie\beheer\ArchiefMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenBeoordelingenLijst;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenBeoordelingenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenLijst;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\PrullenbakMaaltijdenTable;
use CsrDelft\view\maalcie\forms\AanmeldingForm;
use CsrDelft\view\maalcie\forms\MaaltijdForm;
use CsrDelft\view\maalcie\forms\RepetitieMaaltijdenForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerMaaltijdenController extends AbstractController {
	/**
	 * @var MaaltijdenModel
	 */
	private $maaltijdenModel;
	/**
	 * @var MaaltijdRepetitiesModel
	 */
	private $maaltijdRepetitiesModel;
	/**
	 * @var MaaltijdAanmeldingenModel
	 */
	private $maaltijdAanmeldingenModel;

	public function __construct(MaaltijdenModel $maaltijdenModel, MaaltijdRepetitiesModel $maaltijdRepetitiesModel, MaaltijdAanmeldingenModel $maaltijdAanmeldingenModel) {
		$this->maaltijdenModel = $maaltijdenModel;
		$this->maaltijdRepetitiesModel = $maaltijdRepetitiesModel;
		$this->maaltijdAanmeldingenModel = $maaltijdAanmeldingenModel;
	}

	public function GET_prullenbak() {
		return view('maaltijden.pagina', [
			'titel' => 'Prullenbak maaltijdenbeheer',
			'content' => new PrullenbakMaaltijdenTable(),
		]);
	}

	public function POST_prullenbak() {
		$data = $this->maaltijdenModel->find('verwijderd = true');
		return new BeheerMaaltijdenLijst($data);
	}

	public function POST_beheer(Request $request) {
		$filter = $request->query->get('filter', '');
		switch ($filter) {
			case 'prullenbak':
				$data = $this->maaltijdenModel->find('verwijderd = true');
				break;
			case 'onverwerkt':
				$data = $this->maaltijdenModel->find('verwijderd = false AND gesloten = true AND verwerkt = false');
				break;
			case 'alles':
				$data = $this->maaltijdenModel->getMaaltijden();
				break;
			case 'toekomst':
			default:
				$data = $this->maaltijdenModel->getMaaltijden('datum > NOW() - INTERVAL 1 WEEK');
				break;
		}

		return new BeheerMaaltijdenLijst($data);
	}

	public function GET_beheer($mid = null) {
		$modal = null;
		if ($mid !== null) {
			$modal = $this->bewerk($mid);
		}
		/** @var MaaltijdRepetitie[] $repetities */
		$repetities = $this->maaltijdRepetitiesModel->find();
		return view('maaltijden.pagina', [
			'titel' => 'Maaltijdenbeheer',
			'content' => new BeheerMaaltijdenTable($repetities),
			'modal' => $modal,
		]);
	}

	public function GET_archief() {
		return view('maaltijden.pagina', [
			'titel' => 'Archief maaltijdenbeheer',
			'content' => new ArchiefMaaltijdenTable(),
		]);
	}

	public function POST_archief(ArchiefMaaltijdModel $archiefMaaltijdModel) {
		$data = $archiefMaaltijdModel->find();
		return new BeheerMaaltijdenLijst($data);
	}

	public function toggle($mid) {
		$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);

		if ($maaltijd->verwerkt) {
			throw new CsrGebruikerException('Maaltijd al verwerkt');
		}

		if ($maaltijd->gesloten) {
			$this->maaltijdenModel->openMaaltijd($maaltijd);
		} else {
			$this->maaltijdenModel->sluitMaaltijd($maaltijd);
		}

		return new BeheerMaaltijdenLijst(array($maaltijd));
	}

	public function nieuw(Request $request) {
		$maaltijd = new Maaltijd();
		$form = new MaaltijdForm($maaltijd, 'nieuw');

		if ($form->validate()) {
			$maaltijd_aanmeldingen = $this->maaltijdenModel->saveMaaltijd($maaltijd);
			if ($maaltijd_aanmeldingen[1] > 0) {
				setMelding($maaltijd_aanmeldingen[1] . ' aanmelding' . ($maaltijd_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $maaltijd_aanmeldingen[0]->aanmeld_filter, 2);
			}
			return new BeheerMaaltijdenLijst(array($maaltijd_aanmeldingen[0]));
		} elseif ($request->query->has('mrid')) {
			$mrid = $request->query->get('mrid');
			$repetitie = $this->maaltijdRepetitiesModel->getRepetitie($mrid);
			$beginDatum = $repetitie->getFirstOccurrence();
			if ($repetitie->periode_in_dagen > 0) {
				return new RepetitieMaaltijdenForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			} else {
				$maaltijd->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
				$maaltijd->product_id = $repetitie->product_id;
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd;
				$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
				return new MaaltijdForm($maaltijd, 'nieuw');
			}
		} else {
			return $form;
		}

	}

	public function bewerk($mid = null) {
		if ($mid === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new ResourceNotFoundException();
			}
			$mid = $selection[0];
		}

		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($mid);
		$form = new MaaltijdForm($maaltijd, 'bewerk');
		if ($form->validate()) {
			$this->maaltijdenModel->update($maaltijd);
			return new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			return $form;
		}
	}

	public function verwijder() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($selection[0]);

		if ($maaltijd->verwijderd) {
			$this->maaltijdenModel->delete($maaltijd);
		} else {
			$maaltijd->verwijderd = true;
			$this->maaltijdenModel->update($maaltijd);
		}

		return new RemoveRowsResponse(array($maaltijd));
	}

	public function herstel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($selection[0]);

		$maaltijd->verwijderd = false;
		$this->maaltijdenModel->update($maaltijd);
		return new RemoveRowsResponse(array($maaltijd)); // Verwijder uit prullenbak
	}

	public function aanmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$this->maaltijdAanmeldingenModel->aanmeldenVoorMaaltijd($maaltijd, $values['voor_lid'], LoginModel::getUid(), $values['aantal_gasten'], true);
			return new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			return $form;
		}
	}

	public function afmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, false); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$this->maaltijdAanmeldingenModel->afmeldenDoorLid($maaltijd, $values['voor_lid'], true);
			return new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			return $form;
		}
	}

	public function leegmaken() {
		$aantal = $this->maaltijdenModel->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' maaltijd' : ' maaltijden') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		return $this->redirectToRoute('maalcie-beheer-maaltijden-prullenbak');
	}

	public function GET_beoordelingen() {
		return view('maaltijden.maaltijd.maaltijd_beoordelingen', [
			'table' => new BeheerMaaltijdenBeoordelingenTable(),
		]);
	}

	public function POST_beoordelingen() {
        $maaltijden = $this->maaltijdenModel->getMaaltijden('datum <= CURDATE()');
        if (!LoginModel::mag(P_MAAL_MOD)) {
        	// Als bekijker geen MaalCie-rechten heeft, toon alleen maaltijden waarvoor persoon sluitrechten had (kok)
					$maaltijden = array_filter($maaltijden->fetchAll(), function ($maaltijd) {
						/** @var Maaltijd $maaltijd */
						return $maaltijd->magSluiten(LoginModel::getUid());
					});
				}
        return new BeheerMaaltijdenBeoordelingenLijst($maaltijden);
	}

	// Repetitie-Maaltijden ############################################################

	public function aanmaken($mrid) {
		$repetitie = $this->maaltijdRepetitiesModel->getRepetitie($mrid);
		$form = new RepetitieMaaltijdenForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijden = $this->maaltijdenModel->maakRepetitieMaaltijden($repetitie, strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (empty($maaltijden)) {
				throw new CsrGebruikerException('Geen nieuwe maaltijden aangemaakt.');
			}
			return new BeheerMaaltijdenLijst($maaltijden);
		} else {
			return $form;
		}
	}

	// Maalcie-fiscaat

	public function onverwerkt() {
		return view('maaltijden.pagina', [
			'titel' => 'Onverwerkte Maaltijden',
			'content' => new OnverwerkteMaaltijdenTable(),
		]);
	}
}
