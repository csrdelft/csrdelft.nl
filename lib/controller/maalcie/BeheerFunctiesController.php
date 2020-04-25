<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeKwalificatiesRepository;
use CsrDelft\view\maalcie\corvee\functies\FunctieDeleteView;
use CsrDelft\view\maalcie\corvee\functies\FunctieForm;
use CsrDelft\view\maalcie\corvee\functies\KwalificatieForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerFunctiesController {
	/**
	 * @var CorveeFunctiesRepository
	 */
	private $corveeFunctiesRepository;
	/**
	 * @var CorveeKwalificatiesRepository
	 */
	private $corveeKwalificatiesRepository;

	public function __construct(CorveeFunctiesRepository $corveeFunctiesRepository, CorveeKwalificatiesRepository $corveeKwalificatiesRepository) {
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveeKwalificatiesRepository = $corveeKwalificatiesRepository;
	}

	public function beheer($fid = null) {
		$fid = (int)$fid;
		$modal = null;
		if ($fid > 0) {
			$modal = $this->bewerken($fid);
		}
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		return view('maaltijden.functie.beheer_functies', ['functies' => $functies, 'modal' => $modal]);
	}

	public function toevoegen() {
		$functie = $this->corveeFunctiesRepository->nieuw();
		$form = new FunctieForm($functie, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$id = $this->corveeFunctiesRepository->save($functie);
			$functie->functie_id = (int)$id;
			setMelding('Toegevoegd', 1);
			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			return $form;
		}
	}

	public function bewerken($fid) {
		$functie = $this->corveeFunctiesRepository->get((int)$fid);
		$form = new FunctieForm($functie, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$rowCount = $this->corveeFunctiesRepository->save($functie);
			if ($rowCount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			return $form;
		}
	}

	public function verwijderen($fid) {
		$functie = $this->corveeFunctiesRepository->get((int)$fid);
		$this->corveeFunctiesRepository->removeFunctie($functie);
		setMelding('Verwijderd', 1);
		return new FunctieDeleteView($fid);
	}

	/**
	 * @param $fid
	 * @return KwalificatieForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function kwalificeer($fid) {
		$functie = $this->corveeFunctiesRepository->get((int)$fid);
		$kwalificatie = $this->corveeKwalificatiesRepository->nieuw($functie);
		$form = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($form->validate()) {
			$this->corveeKwalificatiesRepository->kwalificatieToewijzen($kwalificatie);
			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			return $form;
		}
	}

	/**
	 * @param $fid
	 * @param $uid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function dekwalificeer($fid, $uid) {
		$functie = $this->corveeFunctiesRepository->get((int)$fid);
		$this->corveeKwalificatiesRepository->kwalificatieIntrekken($uid, $functie->functie_id);
		return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
	}

}
