<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BiebCatalogus;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;

class BibliotheekCatalogusDatatableContent extends AbstractBibliotheekView {

	public function __construct(BiebCatalogus $catalogus) {
		parent::__construct($catalogus);
	}

	public function view() {
		/*
     * Output
     */
		$output = array(
			"sEcho" => intval(filter_input(INPUT_GET, 'sEcho', FILTER_SANITIZE_NUMBER_INT)),
			"iTotalRecords" => $this->model->getTotaal(),
			"iTotalDisplayRecords" => $this->model->getGefilterdTotaal(),
			"aaData" => array()
		);

		//kolommen van de dataTable
		$aKolommen = $this->model->getKolommen();
		//Vult de array aaData met htmlcontent. Entries van aaData corresponderen met tabelcellen.
		foreach ($this->model->getBoeken() as $aBoek) {
			$boek = array();
			//loopt over de zichtbare kolommen
			for ($i = 0; $i < $this->model->getKolommenZichtbaar(); $i++) {
				//van sommige kolommen wordt de inhoud verfraaid
				switch ($aKolommen[$i]) {
					case 'titel':
						$boek[] = $this->render_titel($aBoek);
						break;
					case 'eigenaar':
					case 'lener':
						$boek[] = $this->render_lidlink($aBoek, $aKolommen[$i]);
						break;
					case 'leningen':
						$boek[] = str_replace(', ', '<br />', $aBoek['leningen']);
						break;
					case 'uitleendatum':
						$boek[] = $this->render_uitleendatum($aBoek);
						break;
					default:
						$boek[] = htmlspecialchars($aBoek[$aKolommen[$i]]);
				}
			}
			$output['aaData'][] = $boek;
		}

		echo json_encode($output);
	}

	/*
   * methodes om htmlinhoud van cellen te maken
   */

	// Geeft html voor titel-celinhoud
	protected function render_titel($aBoek) {
		//urltitle
		$urltitle = 'title="Boek: ' . htmlspecialchars($aBoek['titel']) . '
Auteur: ' . htmlspecialchars($aBoek['auteur']) . ' 
Rubriek: ' . htmlspecialchars($aBoek['categorie']) . '"';
		//url
		if (LoginModel::mag('P_BIEB_READ')) {
			$titel = '<a href="/bibliotheek/boek/' . $aBoek['id'] . '" ' . $urltitle . '>'
				. htmlspecialchars($aBoek['titel'])
				. '</a>';
		} else {
			$titel = htmlspecialchars($aBoek['titel']);
		}
		return $titel;
	}

	//Geeft html voor lener- of eigenaar-celinhoud
	protected function render_lidlink(
		$aBoek,
		$key
	) {
		$aUid = explode(', ', $aBoek[$key]);
		$sNaamlijst = '';
		foreach ($aUid as $uid) {
			if ($uid == 'x222') {
				$sNaamlijst .= 'C.S.R.-bibliotheek';
			} else {
				$naam = ProfielModel::getLink($uid, 'civitas');
				if ($naam) {
					$sNaamlijst .= $naam;
				} else {
					$sNaamlijst .= '-';
				}
			}
			$sNaamlijst .= '<br />';
		}
		return $sNaamlijst;
	}

	//Geeft html voor status-celinhoud
	protected function render_uitleendatum($aBoek) {
		$aStatus = explode(', ', $aBoek['status']);
		$aUitleendatum = explode(', ', $aBoek['uitleendatum']);
		$sUitleendatalijst = '';
		$j = 0;
		foreach ($aUitleendatum as $uitleendatum) {
			//title met omschrijvingstatus
			switch ($aStatus[$j]) {
				case 'uitgeleend':
					$sUitleendatalijst .= '<span title="Uitgeleend sinds ' . strip_tags(reldate($uitleendatum)) . '">';
					break;
				case 'teruggegeven':
					$sUitleendatalijst .= '<span title="Teruggegeven door lener. Uitgeleend sinds ' . strip_tags(reldate($uitleendatum)) . '">';
					break;
				case 'vermist':
					$sUitleendatalijst .= '<span title="Vermist sinds ' . strip_tags(reldate($uitleendatum)) . '">';
					break;
				default:
					$sUitleendatalijst .= '<span title="Exemplaar is beschikbaar">';
			}
			//indicator
			$sUitleendatalijst .= '<span class="biebindicator ' . $aStatus[$j] . '">• </span>';
			//datum
			if ($aStatus[$j] == 'uitgeleend' OR $aStatus[$j] == 'teruggegeven' OR $aStatus[$j] == 'vermist') {
				$sUitleendatalijst .= strftime("%d %b %Y", strtotime($uitleendatum));
			}
			$sUitleendatalijst .= '</span><br />';
			$j++;
		}
		return $sUitleendatalijst;
	}

}
