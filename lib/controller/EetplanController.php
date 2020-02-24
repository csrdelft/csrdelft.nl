<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrNotFoundException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\eetplan\EetplanBekendenRepository;
use CsrDelft\repository\eetplan\EetplanRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenForm;
use CsrDelft\view\eetplan\EetplanBekendeHuizenResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenTable;
use CsrDelft\view\eetplan\EetplanBekendenForm;
use CsrDelft\view\eetplan\EetplanBekendenTable;
use CsrDelft\view\eetplan\EetplanHuizenResponse;
use CsrDelft\view\eetplan\EetplanHuizenTable;
use CsrDelft\view\eetplan\EetplanHuizenZoekenResponse;
use CsrDelft\view\eetplan\NieuwEetplanForm;
use CsrDelft\view\eetplan\VerwijderEetplanForm;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor eetplan.
 */
class EetplanController extends AbstractController {
	/** @var string */
	private $lichting;
	/** @var EetplanRepository */
	private $eetplanRepository;
	/** @var EetplanBekendenRepository */
	private $eetplanBekendenRepository;
	/** @var WoonoordenModel */
	private $woonoordenModel;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;

	public function __construct(
		SerializerInterface $serializer,
		EetplanRepository $eetplanRepository,
		EetplanBekendenRepository $eetplanBekendenModel,
		WoonoordenModel $woonoordenModel
	) {
		$this->eetplanRepository = $eetplanRepository;
		$this->eetplanBekendenRepository = $eetplanBekendenModel;
		$this->woonoordenModel = $woonoordenModel;
		$this->lichting = substr((string)LichtingenModel::getJongsteLidjaar(), 2, 2);
		$this->serializer = $serializer;
	}

	public function view() {
		return view('eetplan.overzicht', [
			'eetplan' => $this->eetplanRepository->getEetplan($this->lichting)
		]);
	}

	/**
	 * @param null $uid
	 * @return View
	 * @throws CsrToegangException
	 */
	public function noviet($uid = null) {
		$eetplan = $this->eetplanRepository->getEetplanVoorNoviet($uid);
		if ($eetplan === false) {
			throw new CsrNotFoundException("Geen eetplan gevonden voor deze noviet");
		}

		return view('eetplan.noviet', [
			'noviet' => ProfielRepository::get($uid),
			'eetplan' => $this->eetplanRepository->getEetplanVoorNoviet($uid)
		]);
	}

	public function huis($id = null) {
		$eetplan = $this->eetplanRepository->getEetplanVoorHuis($id, $this->lichting);
		if ($eetplan == []) {
			throw new CsrGebruikerException('Huis niet gevonden');
		}

		return view('eetplan.huis', [
			'woonoord' => WoonoordenModel::instance()->get($id),
			'eetplan' => $eetplan,
		]);
	}

	public function woonoorden($actie = null) {
		if ($actie == 'toggle') {
			$selection = $this->getDataTableSelection();
			$woonoorden = [];
			foreach ($selection as $woonoord) {
				/** @var Woonoord $woonoord */
				$woonoord = $this->woonoordenModel->retrieveByUUID($woonoord);
				$woonoord->eetplan = !$woonoord->eetplan;
				$this->woonoordenModel->update($woonoord);
				$woonoorden[] = $woonoord;
			}
			return new EetplanHuizenResponse($woonoorden);
		} else {
			$woonoorden = $this->woonoordenModel->find('status = ?', array(GroepStatus::HT));
			return new EetplanHuizenResponse($woonoorden);
		}
	}

	/**
	 * @return View
	 * @throws CsrToegangException
	 */
	public function bekendehuizen() {
		return new EetplanBekendeHuizenResponse($this->eetplanRepository->getBekendeHuizen($this->lichting));
	}

	public function bekendehuizen_toevoegen() {
		$eetplan = new Eetplan();
		$eetplan->avond = '0000-00-00';
		$form = new EetplanBekendeHuizenForm($eetplan, '/eetplan/bekendehuizen/toevoegen');
		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanRepository->exists($eetplan)) {
			setMelding('Deze noviet is al eens op dit huis geweest', -1);
			return $form;
		} else {
			$this->eetplanRepository->create($eetplan);
			return new EetplanBekendeHuizenResponse($this->eetplanRepository->getBekendeHuizen($this->lichting));
		}
	}

	public function bekendehuizen_bewerken($uuid = null) {
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplan = $this->eetplanRepository->retrieveByUUID($uuid);
		$form = new EetplanBekendeHuizenForm($eetplan, '/eetplan/bekendehuizen/bewerken/' . $uuid, true);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanRepository->update($eetplan);
			return new EetplanBekendeHuizenResponse($this->eetplanRepository->getBekendeHuizen($this->lichting));
		} else {
			return $form;
		}
	}

	public function bekendehuizen_verwijderen() {
		$selection = $this->getDataTableSelection();
		$verwijderd = array();
		if ($selection !== false) {
			foreach ($selection as $uuid) {
				$eetplan = $this->eetplanRepository->retrieveByUUID($uuid);
				if ($eetplan === false) continue;
				$this->eetplanRepository->delete($eetplan);
				$verwijderd[] = $eetplan;
			}
		}
		return new RemoveRowsResponse($verwijderd);
	}

	public function bekendehuizen_zoeken(Request $request) {
		$huisnaam = $request->query->get('q');
		$huisnaam = '%' . $huisnaam . '%';
		$woonoorden = $this->woonoordenModel->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
		return new EetplanHuizenZoekenResponse($woonoorden);
	}

	public function novietrelatie() {
		return new GenericDataTableResponse($this->serializer, $this->eetplanBekendenRepository->getBekenden($this->lichting));
	}

	public function novietrelatie_toevoegen() {
		$eetplanbekenden = new EetplanBekenden();
		$form = new EetplanBekendenForm($eetplanbekenden, '/eetplan/novietrelatie/toevoegen');
		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanBekendenRepository->exists($eetplanbekenden)) {
			setMelding('Bekenden bestaan al', -1);
			return $form;
		} else {
			$this->eetplanBekendenRepository->create($eetplanbekenden);
			return new GenericDataTableResponse($this->serializer, $this->eetplanBekendenRepository->getBekenden($this->lichting));
		}
	}

	public function novietrelatie_bewerken($uuid) {
		if (!$uuid) {
			$uuid = $this->getDataTableSelection()[0];
		}

		$eetplanbekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
		$form = new EetplanBekendenForm($eetplanbekenden, '/eetplan/novietrelatie/bewerken/' . $uuid, true);
		if ($form->isPosted() && $form->validate()) {
			$this->eetplanBekendenRepository->update($eetplanbekenden);
			return new GenericDataTableResponse($this->serializer, $this->eetplanBekendenRepository->getBekenden($this->lichting));
		} else {
			return $form;
		}
	}

	public function novietrelatie_verwijderen() {
		$selection = $this->getDataTableSelection();
		$verwijderd = [];
		foreach ($selection as $uuid) {
			$bekenden = $this->eetplanBekendenRepository->retrieveByUUID($uuid);
			$this->eetplanBekendenRepository->delete($bekenden);
			$verwijderd[] = new RemoveDataTableEntry([$bekenden->uid1, $bekenden->uid2], EetplanBekenden::class);
		}
		return new GenericDataTableResponse($this->serializer, $verwijderd);
	}

	/**
	 * Beheerpagina.
	 *
	 * POST een json body om dingen te doen.
	 */
	public function beheer() {
		return view('eetplan.beheer', [
			'bekendentable' => new EetplanBekendenTable(),
			'huizentable' => new EetplanHuizenTable(),
			'bekendehuizentable' => new EetplanBekendeHuizenTable(),
			'eetplan' => $this->eetplanRepository->getEetplan($this->lichting)
		]);
	}

	public function nieuw() {
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			return $form;
		} elseif ($this->eetplanRepository->ormCount("avond = ?", array($form->getValues()['avond'])) > 0) {
			setMelding('Er bestaat al een eetplan met deze datum', -1);
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->eetplanRepository->maakEetplan($avond, $this->lichting);

			foreach ($eetplan as $sessie) {
				$this->eetplanRepository->create($sessie);
			}

			return view('eetplan.table', ['eetplan' => $this->eetplanRepository->getEetplan($this->lichting)]);
		}
	}

	public function verwijderen() {
		$avonden = $this->eetplanRepository->getAvonden($this->lichting);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			return $form;
		} else {
			$avond = date_create($form->getValues()['avond']);
			$this->eetplanRepository->verwijderEetplan($avond, $this->lichting);

			return view('eetplan.table', ['eetplan' => $this->eetplanRepository->getEetplan($this->lichting)]);
		}
	}
}
