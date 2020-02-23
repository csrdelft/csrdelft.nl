<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\service\ProfielService;

/**
 * C.S.R. Delft | pubcie@csrdelft.nl
 * Streeplijstcontent.php
 *
 * @deprecated
 */
class Streeplijstcontent implements View, ToResponse {
	use ToHtmlResponse;
	private $sVerticale = 'alle';
	private $sLidjaar = '';
	private $aGoederen;
	/** @var Profiel[] */
	private $aLeden;

	function __construct() {
		$this->load();
	}

	function getModel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	function getTitel() {
		return 'Bestel- & inschrijflijst-generator voor C.S.R. Delft';
	}

	function load() {
		if (isset($_GET['goederen']) AND trim($_GET['goederen']) != '') {
			$sGoederen = htmlspecialchars($_GET['goederen']);
		} else {
			$sGoederen = 'Gulpenerbier,Frisdrank,normSB,luxeSB,NeuB,Wijn,Wky,dWky,Sterk,Mix,Port,Repen,Noot,Koek,Tost,FWijn';
		}
		$this->parseGoederen($sGoederen);

		if (isset($_GET['moot']) AND strlen($_GET['moot']) === 1) {
			$this->sVerticale = $_GET['moot'];
		}
		if (isset($_GET['lichting']) AND preg_match('/^\d{2}$/', $_GET['lichting']) == 1) {
			$this->sLidjaar = $_GET['lichting'];
		}
		//leden welke in de lijst moeten laden.
		$profielService = ContainerFacade::getContainer()->get(ProfielService::class);
		$this->aLeden = $profielService->zoekLeden(empty($this->sLidjaar) ? '%' : $this->sLidjaar, 'uid', $this->sVerticale, 'achternaam', 'leden');
	}

	function parseGoederen($sGoederen) {
		$sGoederen = str_replace(array(', ', ',  '), ',', $sGoederen);
		$this->aGoederen = explode(',', $sGoederen);
		if (isset($_GET['sortCols'])) {
			sort($this->aGoederen);
		}
	}

	function getGoederenArray() {
		return $this->aGoederen;
	}

	function goederenCount() {
		return count($this->getGoederenArray());
	}

	function getGoederen() {
		return implode(', ', $this->getGoederenArray());
	}

	function getHtml() {
		$sReturn = '
			<html>
				<head>
					<style>
						body{ font-family:Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 13px; }
						table{ border: 2px solid black; }
						td{ border: 1px solid black; }
						table{ border-collapse: collapse; width: 100%; }
						td.naam{
							border-right: 2px solid black;
							width: 25%; white-space: nowrap;
						}
						td.cell0{  }
						td.cell1{ background-color: darkgrey;}
						thead td{
							border-bottom: 2px solid black;
							border-top: 2px solid black;
							font-weight: bold; padding: 2px;}
						.breekpunt{
							page-break-after: always; }

						input.text { width: 100% }

					</style>
			</head>
			<body><table>';
		//headerregeltje klussen
		$sKop = '<thead><tr><td class="naam">Naam</td>';
		$i = 1;
		foreach ($this->aGoederen as $sArtikel) {
			$sKop .= '<td class="cell';
			//switch the row coloring..
			if (isset($_GET['colorCols'])) {
				$sKop .= ($i % 2);
			} else {
				$sKop .= '0';
			}
			$sKop .= '">' . $sArtikel . '</td>';
			$i++;
		}
		$sKop .= '</tr></thead>';

		//eerte header weergeven.
		$sReturn .= $sKop;

		$iTeller = 2;
		foreach ($this->aLeden as $aLid) {
			if ($iTeller % 43 == 1) {
				$sReturn .= $sKop . '</tr></table>';
				$sReturn .= '<span class="breekpunt"></span>';
				$sReturn .= '<table><tr>' . $sKop;
			}
			$sReturn .= '<tr><td class="naam">' . $aLid->getNaam('streeplijst') . '</td>';
			for ($i = 1; $i <= $this->goederenCount(); $i++) {
				$sReturn .= '<td class="cell' . ($i % 2) . '">&nbsp;</td>';
			}
			$sReturn .= '</tr>' . "\r\n";
			$iTeller++;
		}
		$sReturn .= $sKop;
		$sReturn .= '</table>';

		return $sReturn;
	}

	function getPdf() {

	}

	function getUrl() {
		$sReturn = 'streeplijst?goederen=' . urlencode($this->getGoederen()) .
			'&moot=' . $this->sVerticale . '&lichting=' . $this->sLidjaar . '&';
		if (isset($_GET['colorCols'])) {
			$sReturn .= 'colorCols&';
		}
		if (isset($_GET['sortCols'])) {
			$sReturn .= 'sortCols&';
		}
		return $sReturn;
	}

	function view() {
		echo '<h1>' . $this->getTitel() . '</h1>
			<form id="streeplijst" action="streeplijst" method="get">
			<fieldset>
				<legend>Bestellijst</legend>
				<br />
				<strong>Goederen:</strong> (Voer goederen in gescheiden door een komma.)<br />
				<input type="text" name="goederen" value="' . $this->getGoederen() . '" style="width: 100%;" /><br />
				<br />
			</fieldset>
			<br />
			<fieldset>
				<legend>Ledenselectie</legend><br />';
		//verticaleselectie
		echo '<strong>Verticale:</strong><br />';
		$verticalen = array('alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');
		foreach ($verticalen as $letter) {
			echo '<input type="radio" name="moot" id="m' . $letter . '" value="' . $letter . '" ';
			if ($letter == $this->sVerticale || ($letter === 'alle' && empty($this->sVerticale))) {
				echo 'checked="checked" ';
			}
			echo '/> <label for="m' . $letter . '">';
			if ($letter == 'alle') {
				echo $letter;
			} else {
				echo VerticalenModel::instance()->get($letter)->naam;
			}
			echo '</label>';
		}
		echo '<br />';
		//lichtingsselectie
		echo '<strong>Lichting:</strong><br />';
		$jaren = array_merge(array('alle'), range(date('Y') - 7, date('Y')));
		foreach ($jaren as $jaar) {
			echo '<input type="radio" name="lichting" id="l' . $jaar . '" value="' . ($jaar === 'alle' ? $jaar : substr($jaar, 2)) . '" ';
			if (substr($jaar, 2) == $this->sLidjaar || ($jaar == 'alle' && empty($this->sLidjaar))) {
				echo 'checked="checked" ';
			}
			echo '/> <label for="l' . $jaar . '">' . $jaar . '</label>';
		}
		echo '</fieldset>
			<br />
			<fieldset>
				<legend>Leguit</legend>
				<input type="checkbox" name="colorCols" id="colorCols" value="" checked="checked" />
				<label for="colorCols">Kolommen om en om grijs maken.</label><br />
				<input type="checkbox" name="sortCols" id="sortCols" value="" />
				<label for="sortCols">Goederen alfabetisch sorteren.</label><br />
				<br /><input type="submit" name="toon" value="Laeden" />
			</fieldset>
			</form>';
		echo '<br />
			Aandachtspunten bij printen via Firefox (andere browsers nog geen uitleg beschikbaar):
			<ul>
				<li>In de <i>Bestand</i> > <i>Pagina-instellingen</i>:
				<ul>
					<li>A4 selecteren als juiste papiergrootte<br /> </li>
				</ul>
				</li>
				<li>In de <i>Opties</i> van <i>Afdruk</i>instellingen (verschijnt via <i>Bestand</i> > <i>Afdrukken...</i>)
				<ul>
					<li>De headers en footers allemaal op <i>blanco</i> zetten</li>
					<li>Achtergrondkleuren afdrukken <i>aan</i>vinken</li>
				</ul>
				</li>
			</ul><br />';
		if (isset($_GET['toon'])) {
			echo '<a href="' . $this->getUrl() . 'iframe">Alleen de streeplijst</a><br />';
			//iframe met html meuk...
			echo '<iframe style="width: 100%; height: 400px;" src="' . $this->getUrl() . 'iframe"></iframe>';
		}
	}

}
