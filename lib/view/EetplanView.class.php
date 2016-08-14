<?php

require_once 'model/EetplanModel.class.php';

/**
 * EetplanView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Weergeven van eetplan.
 */
abstract class AbstractEetplanView extends SmartyTemplateView {

	protected $aEetplan;

	public function getTitel() {
		return 'Eetplan';
	}

	public function getBreadcrumbs() {
		return '<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a> » <a href="/eetplan">Eetplan</a>';
	}

}

class EetplanView extends AbstractEetplanView {

	public function __construct(EetplanModel $model) {
		parent::__construct($model);
		$this->aEetplan = $this->model->getEetplan();
	}

	function view() {
		$aToonAvonden = array(1, 2, 3, 4);
		$aHuizenArray = $this->model->getHuizen();
		echo '
			<h1>Eetplan</h1>
			<div class="geelblokje"><h3>LET OP: </h3>
				Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.
			</div>
			<table class="eetplantabel">
			<tr><th style="width: 200px;">Noviet/Avond</td>';
		//kopjes voor tabel
		foreach ($aToonAvonden as $iTeller) {
			echo '<th class="huis">' . $this->model->getDatum($iTeller) . '</th>';
		}
		echo '</tr>';
		$row = 0;
		foreach ($this->aEetplan as $aEetplanVoorPheut) {
			echo '<tr class="kleur' . ($row % 2) . '"><td><a href="/eetplan/noviet/' . $aEetplanVoorPheut[0]['uid'] . '">' . $aEetplanVoorPheut[0]['naam'] . '</a></td>';
			foreach ($aToonAvonden as $iTeller) {
				$huisnaam = $aHuizenArray[$aEetplanVoorPheut[$iTeller] - 1]['huisNaam'];
				$huisnaam = str_replace(array('Huize ', 'De ', 'Villa '), '', $huisnaam);
				$huisnaam = substr($huisnaam, 0, 18);

				echo '<td class="huis"><a href="/eetplan/huis/' . $aEetplanVoorPheut[$iTeller] . '">' .
				htmlspecialchars($huisnaam) .
				'</a></td>';
			}
			echo '</tr>';
			$row++;
		}
		echo '</table>';
	}

}

class EetplanNovietView extends AbstractEetplanView {

	private $uid;

	public function __construct(EetplanModel $model, $uid) {
		parent::__construct($model);
		$this->uid = $uid;
		$this->aEetplan = $this->model->getEetplanVoorPheut($this->uid);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . ProfielModel::getLink($this->uid, 'civitas');
	}

	function view() {
		//huizen voor een feut tonen
        $this->smarty->assign('eetplan', $this->aEetplan);
        $this->smarty->assign('model', $this->model);
        $this->smarty->display('eetplan/noviet.tpl');
	}

}

class EetplanHuisView extends AbstractEetplanView {

	private $woonoord;

	public function __construct(EetplanModel $model, $iHuisID) {
		parent::__construct($model);
		$this->aEetplan = $this->model->getEetplanVoorHuis($iHuisID);
		$this->woonoord = WoonoordenModel::omnummeren($this->aEetplan[0]['groepid']);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/groepen/woonoorden/' . $this->woonoord->id . '">' . $this->woonoord->naam . '</a>';
	}

	function view() {
		//feuten voor een huis tonen
        $this->smarty->assign('model', $this->model);
        $this->smarty->assign('eetplan', $this->aEetplan);
        $this->smarty->display('eetplan/huis.tpl');
	}
}

class EetplanBeheerView extends AbstractEetplanView {
    public function __construct(EetplanModel $model)
    {
        parent::__construct($model);
        $this->aEetplan = $this->model->getEetplan();
    }

    public function getBreadcrumbs() {
        return parent::getBreadcrumbs() . ' » <span>Beheer</span>';
    }

    public function view() {
        echo "eetplanbeheer";
    }
}
