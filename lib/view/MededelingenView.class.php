<?php

class MededelingView extends SmartyTemplateView {

	private $prullenbak;
	/**
	 * @var MededelingenModel
	 */
	protected $model;

	/**
	 * @var Mededeling
	 */
	private $mededeling;


	public function __construct(Mededeling $mededeling, $prullenbak = false) {
		parent::__construct(MededelingenModel::instance(), 'Mededelingen');
		$this->prullenbak = $prullenbak;
		$this->mededeling = $mededeling;

		$this->smarty->assign('prullenbak', $this->prullenbak);

		
	}

	public function getBreadcrumbs() {
		$breadcrumb = parent::getBreadcrumbs() . '<a href="/mededelingen">Mededelingen</a> » ';
		if ($this->mededeling->id) {
			$breadcrumb .= '<a href="/mededelingen/'.$this->mededeling->id.'">' . $this->mededeling->titel . '</a> » <span class="active">Bewerken</span>';
		} else {
			$breadcrumb .= '<span class="active">Toevoegen</span>';
		}
		return $breadcrumb;
	}

	public function view() {
		$this->smarty->assign('mededeling', $this->mededeling);
		$this->smarty->assign('prioriteiten', MededelingenModel::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->mededeling->vervaltijd === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}
		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}

class MededelingenView extends SmartyTemplateView {

	/**
	 * @var Mededeling
	 */
	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	private $prullenbak;

	const aantalTopMostBlock = 3;
	const mededelingenRoot = '/mededelingen/';

	/**
	 * @var MededelingenModel
	 */
	protected $model;

	public function __construct($mededelingId, $paginanummer = null, $prullenbak = false) {
		parent::__construct(MededelingenModel::instance(), 'Mededelingen');
		$this->prullenbak = $prullenbak;


		$this->geselecteerdeMededeling = null;
		if ($paginanummer !== null) {
			$this->paginaNummer = $paginanummer;
			$this->paginaNummerOpgevraagd = true;
		} else {
			$this->paginaNummer = 1;
		}

		if ($mededelingId != 0) {
			try {
				$this->geselecteerdeMededeling = $this->model->getUUID($mededelingId);;
				if (!$this->prullenbak OR ! LoginModel::mag('P_NEWS_MOD')) {
					// In de volgende gevallen heeft de gebruiker geen rechten om deze mededeling te bekijken:
					// 1. Indien deze mededeling reeds verwijderd is.
					// 2. Indien deze mededeling niet bestemd is voor iedereen en de gebruiker geen leden-lees rechten heeft.
					// 3. Indien deze mededeling alleen bestemd is voor leden en de gebruiker een oudlid is.
					// 4. Indien deze mededeling verborgen is en de gebruiker geen moderator is.
					// 5. Indien deze mededeling wacht op goedkeuring en de gebruiker geen moderator is EN deze mededeling niet van hem is.
					if (($this->geselecteerdeMededeling->zichtbaarheid == 'verwijderd')
						OR ( $this->geselecteerdeMededeling->prive AND ! LoginModel::mag('P_LEDEN_READ'))
						OR ( $this->geselecteerdeMededeling->doelgroep == 'leden' AND LoginModel::mag('P_ALLEEN_OUDLID'))
						OR ( $this->geselecteerdeMededeling->zichtbaarheid == 'onzichtbaar' AND ! LoginModel::mag('P_NEWS_MOD'))
						OR ( $this->geselecteerdeMededeling->zichtbaarheid == 'wacht_goedkeuring' AND ( (LoginModel::getUid() != $this->geselecteerdeMededeling->uid) AND !LoginModel::mag('P_NEWS_MOD') )
							)
					) {
						// De gebruiker heeft geen rechten om deze mededeling te bekijken, dus we resetten het weer.
						$this->geselecteerdeMededeling = null;
					}
				}
			} catch (Exception $e) {
				// Doe niets, zodat $geselecteerdeMededeling gelijk blijft aan null.
			}
		}
		if ($this->geselecteerdeMededeling === null) {
			// Als er minstens één 'topmost' mededeling is, maak dat de geselecteerde.
			// Anders, hou $this->geselecteerdeMededeling gelijk aan null.
			$topMost = $this->model->getTopmost(self::aantalTopMostBlock); // Haal de n belangrijkste mededelingen op.
			if (isset($topMost[0])) {
				$this->geselecteerdeMededeling = $topMost[0];
			}
		}

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function getBreadcrumbs()
	{
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
		$this->smarty->assign('mededelingenketser_root', self::mededelingenRoot);
		// Het pad naar de paginaroot (mededelingenketser of prullenbak).
		if (!$this->prullenbak) {
			$this->smarty->assign('pagina_root', self::mededelingenRoot);
		} else {
			$this->smarty->assign('pagina_root', self::mededelingenRoot . 'prullenbak/');
		}
		$this->smarty->assign('model', $this->model);
		$this->smarty->assign('lijst', $this->model->getLijstVanPagina($this->paginaNummer, LidInstellingen::get('mededelingen', 'aantalPerPagina'), $this->prullenbak));
		$this->smarty->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		$this->smarty->assign('wachtGoedkeuring', $this->model->getLijstWachtGoedkeuring());
		$this->smarty->assign('huidigePagina', $this->paginaNummer);
		$this->smarty->assign('totaalAantalPaginas', (ceil($this->model->getAantal($this->prullenbak) / LidInstellingen::get('mededelingen', 'aantalPerPagina'))));
		$this->smarty->assign('datumtijdFormaat', '%d-%m-%Y %H:%M');
		$this->smarty->display('mededelingen/mededelingen.tpl');
	}

	public function getTopBlock($doelgroep) {
		$topMost = $this->model->getTopmost(self::aantalTopMostBlock, $doelgroep);
		$this->smarty->assign('topmost', $topMost);

		return $this->smarty->fetch('mededelingen/mededelingentopblock.tpl');
	}

}

class MededelingenZijbalkView extends SmartyTemplateView {

	public function view() {
		// De laatste n mededelingen ophalen en meegeven aan $this.
		$mededelingen = MededelingenModel::getLaatsteMededelingen($this->model);
		$this->smarty->assign('mededelingen', $mededelingen);

		$this->smarty->display('mededelingen/mededelingenzijbalk.tpl');
	}

}

class MededelingenOverzichtView extends SmartyTemplateView {

	public function __construct() {
		parent::__construct(null, 'Top 3 mededelingenoverzicht');
	}

	public function view() {
		$this->smarty->display('mededelingen/top3overzicht.tpl');
	}

}
