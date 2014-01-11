<?php

class MededelingenContent extends TemplateView {

	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	private $prullenbak;

	const aantalTopMostBlock = 3;
	const mededelingenRoot = '/actueel/mededelingen/';

	public function __construct($mededelingId, $prullenbak = false) {
		parent::__construct();
		$this->prullenbak = $prullenbak;

		$this->geselecteerdeMededeling = null;
		$this->paginaNummer = 1;
		$this->paginaNummerOpgevraagd = false;

		if ($mededelingId != 0) {
			try {
				$this->geselecteerdeMededeling = new Mededeling($mededelingId);
				if (!$this->prullenbak OR !Mededeling::isModerator()) {
					// In de volgende gevallen heeft de gebruiker geen rechten om deze mededeling te bekijken:
					// 1. Indien deze mededeling reeds verwijderd is.
					// 2. Indien deze mededeling niet bestemd is voor iedereen en de gebruiker geen leden-lees rechten heeft.
					// 3. Indien deze mededeling alleen bestemd is voor leden en de gebruiker een oudlid is.
					// 4. Indien deze mededeling verborgen is en de gebruiker geen moderator is.
					// 5. Indien deze mededeling wacht op goedkeuring en de gebruiker geen moderator is EN deze mededeling niet van hem is. 
					if (
							($this->geselecteerdeMededeling->getZichtbaarheid() == 'verwijderd') OR
							($this->geselecteerdeMededeling->isPrive() AND !LoginLid::instance()->hasPermission('P_LEDEN_READ')) OR
							($this->geselecteerdeMededeling->getDoelgroep() == 'leden' AND Mededeling::isOudlid()) OR
							($this->geselecteerdeMededeling->getZichtbaarheid() == 'onzichtbaar' AND !Mededeling::isModerator()) OR
							($this->geselecteerdeMededeling->getZichtbaarheid() == 'wacht_goedkeuring' AND
							( (LoginLid::instance()->getUid() != $this->geselecteerdeMededeling->getUid()) AND
							!Mededeling::isModerator() )
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
			$topMost = Mededeling::getTopmost(self::aantalTopMostBlock); // Haal de n belangrijkste mededelingen op.
			if (isset($topMost[0])) {
				$this->geselecteerdeMededeling = $topMost[0];
			}
		}
	}

	public function setPaginaNummer($pagina) {
		if (is_numeric($pagina) AND $pagina >= 1) {
			$this->paginaNummerOpgevraagd = true;
			$this->paginaNummer = $pagina;
		}
	}

	public function getTitel() {
		return 'Mededelingen overzicht';
	}

	public function view() {
		if (!$this->paginaNummerOpgevraagd) {
			$this->paginaNummer = $this->geselecteerdeMededeling->getPaginaNummer($this->prullenbak);
		}

		$this->assign('melding', $this->getMelding());
		$this->assign('prullenbak', $this->prullenbak);

		// De link om terug te gaan naar de mededelingenketser.
		$this->assign('mededelingenketser_root', self::mededelingenRoot);
		// Het pad naar de paginaroot (mededelingenketser of prullenbak).
		if (!$this->prullenbak) {
			$this->assign('pagina_root', self::mededelingenRoot);
		} else {
			$this->assign('pagina_root', self::mededelingenRoot . 'prullenbak/');
		}

		$this->assign('lijst', Mededeling::getLijstVanPagina($this->paginaNummer, Instellingen::get('mededelingen_aantalPerPagina'), $this->prullenbak));
		$this->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		$this->assign('wachtGoedkeuring', Mededeling::getLijstWachtGoedkeuring());

		$this->assign('huidigePagina', $this->paginaNummer);
		$this->assign('totaalAantalPaginas', (ceil(Mededeling::getAantal($this->prullenbak) / Instellingen::get('mededelingen_aantalPerPagina'))));

		$this->assign('datumtijdFormaat', '%d-%m-%Y %H:%M');

		$this->display('mededelingen/mededelingen.tpl');
	}

	public function getTopBlock($doelgroep) {


		$topMost = Mededeling::getTopmost(self::aantalTopMostBlock, $doelgroep);

		$this->assign('mededelingenRoot', self::mededelingenRoot);
		$this->assign('topmost', $topMost);

		return $this->fetch('mededelingen/mededelingentopblock.tpl');
	}

}

class MededelingenZijbalkContent extends TemplateView {

	private $aantal;

	public function __construct($aantal) {
		parent::__construct();
		$this->aantal = (int) $aantal;
	}

	public function view() {


		// Handige variabelen.
		$this->assign('mededelingenRoot', MededelingenContent::mededelingenRoot);

		// De laatste n mededelingen ophalen en meegeven aan $this.
		$mededelingen = Mededeling::getLaatsteMededelingen($this->aantal);
		$this->assign('mededelingen', $mededelingen);

		$this->display('mededelingen/mededelingenzijbalk.tpl');
	}

}

?>
