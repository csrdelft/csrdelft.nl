<?php

namespace CsrDelft\view\mededelingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\mededelingen\Mededeling;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * Class MededelingenView
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class MededelingenView extends SmartyTemplateView {

	/**
	 * @var Mededeling
	 */
	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	private $prullenbak;

	const AANTAL_TOP_MOST_BLOK = 3;
	const MEDEDELINGEN_ROOT = '/mededelingen/';

	/**
	 * @var MededelingenModel
	 */
	protected $model;

	/**
	 * MededelingenView constructor.
	 *
	 * @param int $mededelingId
	 * @param int $paginanummer
	 * @param bool $prullenbak
	 */
	public function __construct(
		$mededelingId,
		$paginanummer = null,
		$prullenbak = false
	) {
		parent::__construct(MededelingenModel::instance(), 'Mededelingen');
		$this->prullenbak = $prullenbak;

		$this->geselecteerdeMededeling = null;
		if (empty($paginanummer)) {
			$this->paginaNummer = 1;
		} else {
			$this->paginaNummer = $paginanummer;
			$this->paginaNummerOpgevraagd = true;
		}

		if ($mededelingId != 0) {
			try {
				$this->geselecteerdeMededeling = $this->model->retrieveByUUID($mededelingId);;
				if (!$this->geselecteerdeMededeling) {
					throw new CsrGebruikerException('Mededeling bestaat niet!');
				} elseif (!$this->prullenbak OR !LoginModel::mag('P_NEWS_MOD')) {
					// In de volgende gevallen heeft de gebruiker geen rechten om deze mededeling te bekijken:
					// 1. Indien deze mededeling reeds verwijderd is.
					// 2. Indien deze mededeling niet bestemd is voor iedereen en de gebruiker geen leden-lees rechten heeft.
					// 3. Indien deze mededeling alleen bestemd is voor leden en de gebruiker een oudlid is.
					// 4. Indien deze mededeling verborgen is en de gebruiker geen moderator is.
					// 5. Indien deze mededeling wacht op goedkeuring en de gebruiker geen moderator is EN deze mededeling niet van hem is.
					if (($this->geselecteerdeMededeling->zichtbaarheid == 'verwijderd')
						OR ($this->geselecteerdeMededeling->prive AND !LoginModel::mag('P_LEDEN_READ'))
						OR ($this->geselecteerdeMededeling->doelgroep == 'leden' AND LoginModel::mag('status:oudlid'))
						OR ($this->geselecteerdeMededeling->zichtbaarheid == 'onzichtbaar' AND !LoginModel::mag('P_NEWS_MOD'))
						OR ($this->geselecteerdeMededeling->zichtbaarheid == 'wacht_goedkeuring' AND ((LoginModel::getUid() != $this->geselecteerdeMededeling->uid) AND !LoginModel::mag('P_NEWS_MOD'))
						)
					) {
						// De gebruiker heeft geen rechten om deze mededeling te bekijken, dus we resetten het weer.
						$this->geselecteerdeMededeling = null;
					}
				}
			} catch (CsrException $e) {
				// Doe niets, zodat $geselecteerdeMededeling gelijk blijft aan null.
			}
		}
		if ($this->geselecteerdeMededeling === null) {
			// Als er minstens één 'topmost' mededeling is, maak dat de geselecteerde.
			// Anders, hou $this->geselecteerdeMededeling gelijk aan null.
			$topMost = $this->model->getTopmost(self::AANTAL_TOP_MOST_BLOK); // Haal de n belangrijkste mededelingen op.
			if (isset($topMost[0])) {
				$this->geselecteerdeMededeling = $topMost[0];
			}
		}

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function getBreadcrumbs() {
		if ($this->prullenbak) {
			return parent::getBreadcrumbs() . '<a href="/" tile="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/mededelingen/">Mededelingen</a> » <span class="active">Prullenbak</span>';
		}
		return parent::getBreadcrumbs();
	}

	public function view() {
		if (!$this->paginaNummerOpgevraagd) {
			$this->paginaNummer = $this->model->getPaginaNummer($this->geselecteerdeMededeling, $this->prullenbak);
		}

		// De link om terug te gaan naar de mededelingenketser.
		$this->smarty->assign('mededelingenketser_root', self::MEDEDELINGEN_ROOT);
		// Het pad naar de paginaroot (mededelingenketser of prullenbak).
		if (!$this->prullenbak) {
			$this->smarty->assign('pagina_root', self::MEDEDELINGEN_ROOT);
		} else {
			$this->smarty->assign('pagina_root', self::MEDEDELINGEN_ROOT . 'prullenbak/');
		}
		$this->smarty->assign('model', $this->model);
		$this->smarty->assign('lijst', $this->model->getLijstVanPagina($this->paginaNummer, LidInstellingenModel::get('mededelingen', 'aantalPerPagina'), $this->prullenbak));
		$this->smarty->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		$this->smarty->assign('wachtGoedkeuring', $this->model->getLijstWachtGoedkeuring());
		$this->smarty->assign('huidigePagina', $this->paginaNummer);
		$this->smarty->assign('totaalAantalPaginas', (ceil($this->model->getAantal($this->prullenbak) / LidInstellingenModel::get('mededelingen', 'aantalPerPagina'))));
		$this->smarty->assign('datumtijdFormaat', '%d-%m-%Y %H:%M');
		$this->smarty->display('mededelingen/mededelingen.tpl');
	}

	public function getTopBlock($doelgroep) {
		$topMost = $this->model->getTopmost(self::AANTAL_TOP_MOST_BLOK, $doelgroep);
		$this->smarty->assign('topmost', $topMost);

		return $this->smarty->fetch('mededelingen/mededelingentopblock.tpl');
	}

}
