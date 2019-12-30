<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekImporter;
use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\bibliotheek\BoekRecensie;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatable;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatableResponse;
use CsrDelft\view\bibliotheek\BoekExemplaarFormulier;
use CsrDelft\view\bibliotheek\BoekFormulier;
use CsrDelft\view\bibliotheek\RecensieFormulier;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * BibliotheekController.class.php  |  Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends AbstractController {
	/**
	 * @var BoekExemplaarModel
	 */
	private $boekExemplaarModel;
	/**
	 * @var BoekModel
	 */
	private $boekModel;
	/**
	 * @var BoekRecensieModel
	 */
	private $boekRecensieModel;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(
		BoekExemplaarModel $boekExemplaarModel,
		BoekModel $boekModel,
		BoekRecensieModel $boekRecensieModel,
		CmsPaginaRepository $cmsPaginaRepository
	) {
		$this->boekExemplaarModel = $boekExemplaarModel;
		$this->boekModel = $boekModel;
		$this->boekRecensieModel = $boekRecensieModel;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	public function recensie($boek_id) {
		$recensie = $this->boekRecensieModel->get($boek_id, LoginModel::getUid());
		$formulier = new RecensieFormulier($recensie);
		if ($formulier->validate()) {
			if (!$recensie->magBewerken()) {
				throw new CsrToegangException("Mag recensie niet bewerken");
			} else {
				$recensie->bewerkdatum = getDateTime();
				$this->boekRecensieModel->updateOrCreate($recensie);
				setMelding("Recensie opgeslagen", 0);
			}
		}
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $boek_id]);
	}

	public function rubrieken() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('rubrieken'))]);
	}

	public function wenslijst() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('wenslijst'))]);
	}

	public function catalogustonen() {
		return view('default', ['content' => new BibliotheekCatalogusDatatable()]);
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 */
	public function catalogusdata() {
		/**
		 * @var Boek[] $data
		 */
		$data = $this->boekModel->find()->fetchAll();
		$uid = filter_input(INPUT_GET, "eigenaar", FILTER_SANITIZE_STRING);
		$results = [];
		if ($uid !== null) {
			foreach ($data as $boek) {
				if ($boek->isEigenaar($uid)) {
					$results[] = $boek;
				}
			}
		} else {
			$results = $data;
		}
		return new BibliotheekCatalogusDatatableResponse($results);
	}

	/**
	 * Boek weergeven
	 * @param $boek_id
	 * @return TemplateView|RedirectResponse
	 */
	public function boek($boek_id = null) {
		if ($boek_id == null) {
			$boek = new Boek();
		} else {
			$boek = $this->boekModel->get($boek_id);
		}
		$boekForm = new BoekFormulier($boek);

		if ($boekForm->validate()) {
			if (!$boek->magBewerken()) {
				throw new CsrToegangException('U mag dit boek niet bewerken');
			} else {
				$boekid = $this->boekModel->updateOrCreate($boek);
				if ($boekid !== false) {
					return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $boek_id]);
				}
			}
		}

		$alleRecensies = $boek->getRecensies();
		$andereRecensies = [];
		$mijnRecensie = new BoekRecensie();
		$mijnRecensie->boek_id = $boek->id;
		$exemplaarFormulieren = [];
		foreach ($boek->getExemplaren() as $exemplaar) {
			if ($exemplaar->magBewerken()) {
				$exemplaarFormulieren[$exemplaar->id] = new BoekExemplaarFormulier($exemplaar);
			}
		}
		foreach ($alleRecensies as $recensie) {
			if ($recensie->schrijver_uid == LoginModel::getUid()) {
				$mijnRecensie = $recensie;
			}
			$andereRecensies[] = $recensie;

		}
		$recensieForm = new RecensieFormulier($mijnRecensie);
		return view('bibliotheek.boek', [
			'boek' => $boek,
			'recensies' => $andereRecensies,
			'boekFormulier' => $boekForm,
			'recensieFormulier' => $recensieForm,
			'exemplaarFormulieren' => $exemplaarFormulieren,
		]);
	}

	public function import($boek_id) {
		$boek = $this->boekModel->get($boek_id);
		if (!$boek->isEigenaar()) {
			throw new CsrToegangException();
		} else {
			$importer = new BoekImporter();
			$importer->import($boek);
			$this->boekModel->update($boek);
			return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $boek->id]);
		}
	}


	public function verwijderbeschrijving($boek_id, $uid) {
		$recensie = $this->boekRecensieModel->get($boek_id, $uid);
		if (!$recensie->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
			throw new CsrToegangException();
		} else {
			$this->boekRecensieModel->delete($recensie);
			setMelding('Recensie met succes verwijderd.', 1);

		}
		exit;
	}

	/**
	 * Verwijder boek
	 *
	 * /verwijderboek/id
	 * @param $boek_id
	 * @return RedirectResponse
	 */
	public function verwijderboek($boek_id) {
		$boek = $this->boekModel->get($boek_id);

		if (!$boek->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving', -1);
			return $this->redirectToRoute('bibliotheek-overzicht');
		} else {
			$this->boekModel->delete($boek);
			setMelding('Boek met succes verwijderd.', 1);
			return $this->redirectToRoute('bibliotheek-overzicht');
		}
	}

	public function exemplaar($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if (!$exemplaar->magBewerken()) {
			throw new CsrToegangException("Mag exemplaar niet bewerken");
		}
		$form = new BoekExemplaarFormulier($exemplaar);
		if ($form->validate()) {
			$this->boekExemplaarModel->update($exemplaar);
		}
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar toevoegen
	 * /addexemplaar/$boekid[/$eigenaarid]
	 * @param string $boek_id
	 * @param string|null $uid
	 * @return RedirectResponse
	 */
	public function addexemplaar($boek_id, $uid = null) {
		$boek = $this->boekModel->get($boek_id);
		if (!$boek->magBekijken()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()', -1);
			return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $boek->id]);
		}
		if ($uid == null) {
			$uid = LoginModel::getUid();
		}
		if ($uid != LoginModel::getUid() && !($uid == 'x222' && LoginModel::mag(P_BIEB_MOD))) {
			throw new CsrToegangException('Mag deze eigenaar niet kiezen');
		}
		$this->boekExemplaarModel->addExemplaar($boek, $uid);

		setMelding('Exemplaar met succes toegevoegd.', 1);
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $boek->id]);
	}

	/**
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$exemplaarid
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 */
	public function verwijderexemplaar($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarModel->delete($exemplaar)) {
				setMelding('Exemplaar met succes verwijderd.', 1);
			} else {
				setMelding('Exemplaar verwijderen mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaarvermist/[id]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 */
	public function exemplaarvermist($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);

		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarModel->setVermist($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als vermist.', 1);
			} else {
				setMelding('Exemplaar markeren als vermist mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaargevonden/[id]
	 * @param $exemplaar_id
	 * @return JsonResponse
	 */
	public function exemplaargevonden($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarModel->setGevonden($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als gevonden.', 1);
			} else {
				setMelding('Exemplaar markeren als gevonden mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
	}

	/**
	 * /exemplaaruitlenen/[exemplaarid]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 */
	public function exemplaaruitlenen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		$uid = filter_input(INPUT_POST, 'lener_uid', FILTER_SANITIZE_STRING);
		if (!$exemplaar->isEigenaar()) {
			setMelding('Alleen de eigenaar mag boeken uitlenen', -1);
		} else if (!ProfielModel::existsUid($uid)) {
			setMelding('Incorrecte lener', -1);
		} else if ($this->boekExemplaarModel->leen($exemplaar, $uid)) {
			return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id, '_fragment' => 'exemplaren']);
		} else {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}

		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}


	/**
	 * /exemplaarlenen/[exemplaarid]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 */
	public function exemplaarlenen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if (!$this->boekExemplaarModel->leen($exemplaar, LoginModel::getUid())) {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}
		return $this->redirectToRoute('bibliotheek-boek', ['boek_id' => $exemplaar->getBoek()->id, '_fragment' => 'exemplaren']);
	}


	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 *
	 * /exemplaarteruggegeven/[exemplaarid]
	 * @param $exemplaar_id
	 * @return JsonResponse
	 */
	public function exemplaarteruggegeven($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if ($exemplaar->isUitgeleend() && $exemplaar->uitgeleend_uid == LoginModel::getUid()) {
			if ($this->boekExemplaarModel->terugGegeven($exemplaar)) {
				setMelding('Exemplaar is teruggegeven.', 1);
			} else {
				setMelding('Teruggave van exemplaar melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. ', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->getId());
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 *
	 * /exemplaarterugontvangen/exemplaarid
	 * @param $exemplaar_id
	 * @return JsonResponse
	 */
	public function exemplaarterugontvangen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarModel->get($exemplaar_id);
		if ($exemplaar->isEigenaar() && ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven())) {
			if ($this->boekExemplaarModel->terugOntvangen($exemplaar)) {
				setMelding('Exemplaar terugontvangen.', 1);
			} else {
				setMelding('Exemplaar terugontvangen melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->getId());
	}


	/**
	 * Genereert suggesties voor jquery-autocomplete
	 *
	 * /autocomplete/auteur
	 * @param $zoekveld
	 * @return JsonResponse
	 */
	public function autocomplete($zoekveld) {
		if (isset($_GET['q'])) {
			$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

			$results = $this->boekModel->autocompleteProperty($zoekveld, $zoekterm);
			$data = [];
			foreach ($results as $result) {
				$data[] = ['data' => [$result], 'value' => $result->{$zoekveld}, 'id' => $result->id];
			}
			return new JsonResponse($data);
		} else {
			throw new CsrToegangException();
		}
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = array();
		foreach ($this->boekModel->autocompleteBoek($zoekterm) as $boek) {
			$result[] = array(
				'url' => '/bibliotheek/boek/' . $boek->id,
				'icon' => Icon::getTag('boek'),
				'label' => $boek->auteur,
				'value' => $boek->titel
			);
		}
		return new JsonResponse($result);
	}


}
