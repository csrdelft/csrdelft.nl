<?php

namespace CsrDelft\lid;

use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\View;

require_once 'lid/LidZoeker.php';

abstract class LLWeergave {

	protected $leden;

	public function __construct(LidZoeker $zoeker) {
		$this->leden = $zoeker->getLeden();
		$this->velden = $zoeker->getVelden();
	}

	public abstract function viewHeader();

	public abstract function viewFooter();

	//viewLid print één regel of vakje ofzo.
	public abstract function viewLid(Profiel $profiel);

	public function view() {
		$this->viewHeader();
		foreach ($this->leden as $lid) {
			$this->viewLid($lid);
		}
		$this->viewFooter();
	}

}

/**
 * De 'normale' ledenlijst, zoals het is zoals het was.
 */
class LLLijst extends LLweergave {

	private function viewVeldnamen() {
		echo '<tr>';
		foreach ($this->velden as $veld) {
			echo '<th>' . ucfirst($veld) . '</th>';
		}
		echo '</tr>';
	}

	public function viewHeader() {
		echo '<table class="zoekResultaat" id="zoekResultaat">';
		echo '<thead>';
		$this->viewVeldnamen();
		echo '</thead><tbody>';
	}

	public function viewFooter() {
		echo "</tbody>\n<tfoot>";
		$this->viewVeldnamen();
		echo '</tfoot></table>';

		//fix jQuery datatables op deze tabel.
		$aoColumns = array();
		foreach ($this->velden as $veld) {
			switch ($veld) {
				case 'pasfoto':
					$aoColumns[] = '{"bSortable": false}';
					break;
				case 'email':
				case 'naam':
				case 'kring':
				case 'patroon':
				case 'verticale':
					$aoColumns[] = '{"sType": \'html\'}';
					break;
				case 'woonoord':
					$aoColumns[] = '{"sType": \'html\'}';
					break;
				default:
					$aoColumns[] = 'null';
			}
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$("#zoekResultaat").dataTable({
					"aaSorting": [],
					"bStateSave": true,
					"oLanguage": {
						"sSearch": "Zoeken in selectie:"
					},
					"iDisplayLength": 50,
					"bInfo": false,
					"bLengthChange": false,
					"aoColumns": [<?php echo implode(', ', $aoColumns); ?>]
				});
			});
		</script><?php
	}

	public function viewLid(Profiel $profiel) {
		echo '<tr id="lid' . $profiel->uid . '">';
		foreach ($this->velden as $veld) {
			echo '<td class="' . $veld . '">';
			switch ($veld) {

				case 'email':
					$email = $profiel->getPrimaryEmail();
					if ($email) {
						echo '<a href="mailto:' . $email . '">' . $email . '</a>';
					}
					break;

				case 'adres':
					echo htmlspecialchars($profiel->getAdres());
					break;

				case 'kring':
					$kring = $profiel->getKring();
					if ($kring) {
						echo '<a href="' . $kring->getUrl() . '">' . $kring->naam . '</a>';
					}
					break;

				case 'naam':
					//we stoppen er een verborgen <span> bij waar op gesorteerd wordt door datatables.
					echo '<span class="verborgen">' . $profiel->getNaam('streeplijst') . '</span>';
					echo $profiel->getLink('volledig');
					break;

				case 'pasfoto':
					echo $profiel->getPasfotoTag();
					break;

				case 'patroon':
					$patroon = ProfielModel::get($profiel->patroon);
					if ($patroon) {
						echo $patroon->getLink('volledig');
					} else {
						echo '-';
					}
					break;

				case 'echtgenoot':
					$echtgenoot = ProfielModel::get($profiel->echtgenoot);
					if ($echtgenoot) {
						echo $echtgenoot->getLink('volledig');
					} else {
						echo '-';
					}
					break;

				case 'status':
					echo LidStatus::getDescription($profiel->status);
					break;

				case 'verticale':
					echo htmlspecialchars($profiel->getVerticale()->naam);
					break;

				case 'woonoord':
					echo $profiel->getWoonoord()->naam;
					break;

				case 'linkedin':
				case 'website':
					echo '<a target="_blank" href="' . htmlspecialchars($profiel->$veld) . '">' . htmlspecialchars($profiel->$veld) . '</a>';
					break;

				default:
					try {
						echo htmlspecialchars($profiel->$veld);
					} catch (\Exception $e) {
						echo ' - ';
					}
			}
			echo '</td>';
		}

		echo '</tr>';
	}

}

/**
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLweergave {

	public function viewHeader() {

	}

	public function viewFooter() {

	}

	public function viewLid(Profiel $profiel) {
		echo $profiel->getLink('leeg');
	}

}

/**
 * CSV in een textarea.
 * Eventueel zou het nog geforceerd downloadbaar gemaakt kunnen worden
 */
class LLCSV extends LLweergave {

	public function viewHeader() {
		echo '<textarea class="csv">';
	}

	public function viewFooter() {
		echo '</textarea>';
	}

	public function viewLid(Profiel $profiel) {

		foreach ($this->velden as $veld) {
			$return = '';
			switch ($veld) {

				case 'adres':
					$return .= $profiel->adres . ';';
					$return .= $profiel->postcode . ';';
					$return .= $profiel->woonplaats;
					break;

				case 'naam':
					$return .= $profiel->voornaam . ';';
					$return .= $profiel->tussenvoegsel . ';';
					$return .= $profiel->achternaam;
					break;

				case 'kring':
					$kring = $profiel->getKring();
					if ($kring) {
						$return .= $kring->naam;
					}
					break;

				case 'pasfoto':
					$return .= $profiel->getPasfotoTag();
					break;

				case 'patroon':
					$patroon = ProfielModel::get($profiel->patroon);
					if ($patroon) {
						$return .= $patroon->getNaam('volledig');
					}
					break;

				case 'echtgenoot':
					$echtgenoot = ProfielModel::get($profiel->echtgenoot);
					if ($echtgenoot) {
						$return .= $echtgenoot->getNaam('volledig');
					}
					break;

				case 'adresseringechtpaar':
					if (empty($profiel->adresseringechtpaar)) {
						$return .= $profiel->getNaam('voorletters');
					} else {
						$return .= $profiel->adresseringechtpaar;
					}
					break;

				case 'verticale':
					$return .= $profiel->getVerticale()->naam;
					break;

				case 'woonoord':
					$woonoord = $profiel->getWoonoord();
					if ($woonoord) {
						$return .= $woonoord->naam;
					}
					break;

				default:
					try {
						$return .= $profiel->$veld;
					} catch (\Exception $e) {
						//omit non-existant fields
					}
			}
			echo htmlspecialchars($return) . ';';
		}
		echo "\n";
	}

}

/**
 * Google wil een CSV met kolomnamen erboven.
 */
class LLGoogleCSV extends LLCSV {

}

/**
 *  C.S.R. Delft | pubcie@csrdelft.nl
 *
 * LLWeergave, LLLijst, LLKaartje, LLCSV:
 *    verschillende methode's om dingen in de ledenlijst weer te geven.
 *    Als je een nieuwe weergave erbij wilt klussen maak dan een class
 *    LL<Naam> extends LLWeergave{} aan en voeg die naam toe aan de
 *    array private $weergave in LidZoeker.
 *
 * LedenlijstContent
 *    Algemene View voor de ledenlijst.
 */
class LedenlijstContent implements View {

	/**
	 * Lid-zoeker
	 * @var LidZoeker
	 */
	private $lidzoeker;

	public function __construct(LidZoeker $zoeker) {
		$this->lidzoeker = $zoeker;
	}

	public function getModel() {
		return $this->lidzoeker;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Ledenlijst der Civitas';
	}

	public function viewSelect($name, $options) {
		echo '<select name="' . $name . '" id="f' . $name . '">';
		foreach ($options as $key => $value) {
			echo '<option value="' . htmlspecialchars($key) . '"';
			if ($key == $this->lidzoeker->getRawQuery($name)) {
				echo ' selected="selected"';
			}
			echo '>' . htmlspecialchars($value) . '</option>';
		}
		echo '</select> ';
	}

	public function viewVeldselectie() {
		echo '<label for="veldselectie">Veldselectie: </label>';
		echo '<div id="veldselectie">';
		$velden = $this->lidzoeker->getSelectableVelden();
		foreach ($velden as $key => $veld) {
			echo '<div class="selectVeld">';
			echo '<input type="checkbox" name="velden[]" id="veld' . $key . '" value="' . $key . '" ';
			if (in_array($key, $this->lidzoeker->getSelectedVelden())) {
				echo 'checked="checked" ';
			}
			echo ' />';
			echo '<label for="veld' . $key . '">' . ucfirst($veld) . '</label>';
			echo '</div>';
		}
		echo '</div>';
	}

	public function view() {
		if ($this->lidzoeker->count() > 0) {
			if (strstr(REQUEST_URI, '?') !== false) {
				$url = REQUEST_URI . '&amp;addToGoogleContacts=true';
			} else {
				$url = REQUEST_URI . '?addToGoogleContacts=true';
			}
			echo '<a href="' . $url . '" class="btn float-right" title="Huidige selectie exporteren naar Google Contacts" onclick="return confirm(\'Weet u zeker dat u deze ' . $this->lidzoeker->count() . ' leden wilt importeren in uw Google-contacts?\')"><img src="/plaetjes/knopjes/google.ico" width="16" height="16" alt="tovoegen aan Google contacts" /></a>';
		}
		echo getMelding();
		echo '<h1>' . (LoginModel::getProfiel()->isOudlid() ? 'Oud-leden en l' : 'L') . 'edenlijst </h1>';
		echo '<form id="zoekform" method="get">';
		echo '<label for="q"></label><input type="text" name="q" value="' . htmlspecialchars($this->lidzoeker->getQuery()) . '" /> ';
		echo '<button class="btn submit">Zoeken</button> <a class="btn" id="toggleAdvanced" href="#geavanceerd">Geavanceerd</a>';

		echo '<div id="advanced" class="verborgen">';
		echo '<label for="status">Status:</label>';
		$this->viewSelect('status', array(
			'LEDEN' => 'Leden',
			'NOVIET' => 'Novieten', 'GASTLID' => 'Gastlid',
			'OUDLEDEN' => 'Oudleden',
			'LEDEN|OUDLEDEN' => 'Leden & oudleden', 'ALL' => 'Alles'));
		echo '<br />';
		echo '<label for="weergave">Weergave:</label>';
		$this->viewSelect('weergave', array(
			'lijst' => 'Lijst (standaard)',
			'kaartje' => 'Visitekaartjes',
			'CSV' => 'CSV-bestand'));
		echo '<br />';

		//sorteren op:
		echo '<label for="sort">Sorteer op:</label>';
		$this->viewSelect('sort', $this->lidzoeker->getSortableVelden());
		echo '<br />';

		//selecteer velden
		echo '<div id="veldselectiecontainer">';
		$this->viewVeldselectie();
		echo '</div><br />';

		echo '</div>'; //einde advanced div.
		echo '</form>';

		echo '<hr class="clear" />';

		if ($this->lidzoeker->count() > 0) {
			$class = 'CsrDelft\\lid\\' . $this->lidzoeker->getWeergave();
			$weergave = new $class($this->lidzoeker);
			$weergave->view();
		} elseif ($this->lidzoeker->searched()) {
			echo 'Geen resultaten';
		} else {
			//nog niet gezocht.
		}
		?>
		<script type="text/javascript">
			function updateVeldselectie() {
				if (jQuery('#fweergave').val() === 'kaartje') {
					jQuery('#veldselectiecontainer').hide('fast');
				} else {
					jQuery('#veldselectiecontainer').show('fast');
				}
			}

			jQuery(document).ready(function ($) {
				$('#toggleAdvanced').click(function () {
					adv = $('#advanced');
					adv.toggleClass('verborgen');

					if (adv.hasClass('verborgen')) {
						window.location.hash = '';
						$('#advanced input').attr('disabled', 'disabled');
					} else {
						window.location.hash = 'geavanceerd';
						$('#zoekform').attr('action', '#geavanceerd');
						$('#advanced input').removeAttr('disabled');
						$('#advanced select').removeAttr('disabled');
					}
				});

				if (document.location.hash === '#geavanceerd') {
					$('#advanced').removeClass('verborgen');
				} else {
					$('#advanced input').attr('disabled', 'disabled');
					$('#advanced select').attr('disabled', 'disabled');
				}
				//weergave van selectie beschikbare veldjes
				$('#fweergave').change(function () {
					updateVeldselectie();
					$('#zoekform').submit();
				});
				updateVeldselectie();
			});
		</script>

		<?php
	}

}
