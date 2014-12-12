<?php

class MededelingView extends SmartyTemplateView {

	private $prullenbak;

	public function __construct(MededelingenModel $mededeling, $prullenbak = false) {
		parent::__construct($mededeling, 'Mededelingen');
		$this->prullenbak = $prullenbak;

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function view() {
		$this->smarty->assign('mededeling', $this->model);
		$this->smarty->assign('prioriteiten', MededelingenModel::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->model->getVervaltijd() === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}
		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}

class MededelingenView extends SmartyTemplateView {

	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	private $prullenbak;

	const aantalTopMostBlock = 3;
	const mededelingenRoot = '/mededelingen/';

	public function __construct($mededelingId, $prullenbak = false) {
		parent::__construct(null, 'Mededelingen overzicht');
		$this->prullenbak = $prullenbak;

		$this->geselecteerdeMededeling = null;
		$this->paginaNummer = 1;
		$this->paginaNummerOpgevraagd = false;

		if ($mededelingId != 0) {
			try {
				$this->geselecteerdeMededeling = new MededelingenModel($mededelingId);
				if (!$this->prullenbak OR ! MededelingenModel::isModerator()) {
					// In de volgende gevallen heeft de gebruiker geen rechten om deze mededeling te bekijken:
					// 1. Indien deze mededeling reeds verwijderd is.
					// 2. Indien deze mededeling niet bestemd is voor iedereen en de gebruiker geen leden-lees rechten heeft.
					// 3. Indien deze mededeling alleen bestemd is voor leden en de gebruiker een oudlid is.
					// 4. Indien deze mededeling verborgen is en de gebruiker geen moderator is.
					// 5. Indien deze mededeling wacht op goedkeuring en de gebruiker geen moderator is EN deze mededeling niet van hem is. 
					if (
							($this->geselecteerdeMededeling->getZichtbaarheid() == 'verwijderd') OR ( $this->geselecteerdeMededeling->isPrive() AND ! LoginModel::mag('P_LEDEN_READ')) OR ( $this->geselecteerdeMededeling->getDoelgroep() == 'leden' AND MededelingenModel::isOudlid()) OR ( $this->geselecteerdeMededeling->getZichtbaarheid() == 'onzichtbaar' AND ! MededelingenModel::isModerator()) OR ( $this->geselecteerdeMededeling->getZichtbaarheid() == 'wacht_goedkeuring' AND ( (LoginModel::getUid() != $this->geselecteerdeMededeling->getUid()) AND ! MededelingenModel::isModerator() )
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
			$topMost = MededelingenModel::getTopmost(self::aantalTopMostBlock); // Haal de n belangrijkste mededelingen op.
			if (isset($topMost[0])) {
				$this->geselecteerdeMededeling = $topMost[0];
			}
		}

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function setPaginaNummer($pagina) {
		if (is_numeric($pagina) AND $pagina >= 1) {
			$this->paginaNummerOpgevraagd = true;
			$this->paginaNummer = $pagina;
		}
	}

	public function view() {
		if (!$this->paginaNummerOpgevraagd) {
			$this->paginaNummer = $this->geselecteerdeMededeling->getPaginaNummer($this->prullenbak);
		}

		// De link om terug te gaan naar de mededelingenketser.
		$this->smarty->assign('mededelingenketser_root', self::mededelingenRoot);
		// Het pad naar de paginaroot (mededelingenketser of prullenbak).
		if (!$this->prullenbak) {
			$this->smarty->assign('pagina_root', self::mededelingenRoot);
		} else {
			$this->smarty->assign('pagina_root', self::mededelingenRoot . 'prullenbak/');
		}
		$this->smarty->assign('lijst', MededelingenModel::getLijstVanPagina($this->paginaNummer, LidInstellingen::get('mededelingen', 'aantalPerPagina'), $this->prullenbak));
		$this->smarty->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		$this->smarty->assign('wachtGoedkeuring', MededelingenModel::getLijstWachtGoedkeuring());
		$this->smarty->assign('huidigePagina', $this->paginaNummer);
		$this->smarty->assign('totaalAantalPaginas', (ceil(MededelingenModel::getAantal($this->prullenbak) / LidInstellingen::get('mededelingen', 'aantalPerPagina'))));
		$this->smarty->assign('datumtijdFormaat', '%d-%m-%Y %H:%M');
		$this->smarty->display('mededelingen/mededelingen.tpl');
	}

	public function getTopBlock($doelgroep) {
		$topMost = MededelingenModel::getTopmost(self::aantalTopMostBlock, $doelgroep);
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
