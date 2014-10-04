<?php
require_once 'lid/lidzoeker.class.php';

/**
 *  C.S.R. Delft | pubcie@csrdelft.nl
 * 
 * LLWeergave, LLLijst, LLKaartje, LLCSV:
 * 		verschillende methode's om dingen in de ledenlijst weer te geven. 
 * 		Als je een nieuwe weergave erbij wilt klussen maak dan een class
 * 		LL<Naam> extends LLWeergave{} aan en voeg die naam toe aan de 
 * 		array private $weergave in LidZoeker.
 * 
 * LedenlijstContent
 * 		Algemene View voor de ledenlijst.
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
			echo '>' . mb_htmlentities($value) . '</option>';
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
		echo '<ul class="horizontal nobullets">
	<li class="active"><a href="/communicatie/ledenlijst/">Ledenlijst</a></li>
	<li><a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a></li>
	<li><a href="/communicatie/verticalen/">Kringen</a></li>
</ul>';
		echo '<hr />';

		if ($this->lidzoeker->count() > 0) {
			if (strstr(REQUEST_URI, '?') !== false) {
				$url = REQUEST_URI . '&amp;addToGoogle=true';
			} else {
				$url = REQUEST_URI . '?addToGoogle=true';
			}
			echo '<a href="' . $url . '" class="knop float-right" title="Huidige selectie exporteren naar Google Contacts" onclick="return confirm(\'Weet u zeker dat u deze ' . $this->lidzoeker->count() . ' leden wilt importeren in uw Google-contacts?\')"><img src="' . CSR_PICS . '/knopjes/google.ico" alt="google"/></a>';
		}
		echo getMelding();
		echo '<h1>' . (LoginModel::instance()->getLid()->isOudlid() ? 'Oud-leden en l' : 'L') . 'edenlijst </h1>';
		echo '<form id="zoekform" method="get">';
		echo '<label for="q"></label><input type="text" name="q" value="' . htmlspecialchars($this->lidzoeker->getQuery()) . '" /> ';
		echo '<input type="submit" class="submit" value="zoeken" /> <a class="knop" id="toggleAdvanced" href="#geavanceerd">Geavanceerd</a>';

		echo '<div id="advanced" class="verborgen">';
		echo '<label for="status">Status:</label>';
		$this->viewSelect('status', array(
			'LEDEN'			 => 'Leden',
			'NOVIET'		 => 'Novieten', 'GASTLID'		 => 'Gastlid',
			'OUDLEDEN'		 => 'Oudleden',
			'LEDEN|OUDLEDEN' => 'Leden & oudleden', 'ALL'			 => 'Alles'));
		echo '<br />';
		echo '<label for="weergave">Weergave:</label>';
		$this->viewSelect('weergave', array(
			'lijst'		 => 'Lijst (standaard)',
			'kaartje'	 => 'Visitekaartjes',
			'CSV'		 => 'CSV-bestand'));
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
			$class = $this->lidzoeker->getWeergave();
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
				if (jQuery('#fweergave').val() == 'kaartje') {
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

				if (document.location.hash == '#geavanceerd') {
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

abstract class LLWeergave {

	protected $leden;

	public function __construct(LidZoeker $zoeker) {
		$this->leden = $zoeker->getLeden();
		$this->velden = $zoeker->getVelden();
	}

	public abstract function viewHeader();

	public abstract function viewFooter();

	//viewLid print één regel of vakje ofzo.
	public abstract function viewLid(Lid $lid);

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

	public function viewLid(Lid $lid) {
		echo '<tr id="lid' . $lid->getUid() . '">';
		foreach ($this->velden as $veld) {
			echo '<td class="' . $veld . '">';
			switch ($veld) {
				case 'adres':
					echo mb_htmlentities($lid->getAdres());
					break;
				case 'kring':
					echo $lid->getKring(true);
					break;
				case 'naam':
					//we stoppen er een verborgen <span> bij waar op gesorteerd wordt door datatables.
					echo '<span class="verborgen">' . $lid->getNaamLink('streeplijst', 'plain') . '</span>';
					echo $lid->getNaamLink('full', 'link');
					break;
				case 'pasfoto':
					if (LidInstellingen::get('forum', 'naamWeergave') === 'Duckstad') {
						echo $lid->getDuckfoto();
					} else {
						echo $lid->getPasfoto();
					}
					break;
				case 'patroon':
					$patroon = $lid->getPatroon();
					if ($patroon instanceof Lid) {
						echo $patroon->getNaamLink('full', 'link');
					} else {
						echo '-';
					}
					break;
				case 'echtgenoot':
					$echtgenoot = $lid->getEchtgenoot();
					if ($echtgenoot instanceof Lid) {
						echo $echtgenoot->getNaamLink('full', 'link');
					} else {
						echo '-';
					}
					break;
				case 'status':
					echo $lid->getStatus()->getDescription();
					break;
				case 'verticale':
					echo mb_htmlentities($lid->getVerticale()->naam);
					break;
				case 'woonoord':
					echo $lid->getWoonoord();
					break;
				case 'linkedin':
				case 'website':
					echo '<a href="' . mb_htmlentities($lid->getProperty($veld)) . '" class="linkExt">' . mb_htmlentities($lid->getProperty($veld)) . '</a>';
					break;
				default:
					try {
						echo mb_htmlentities($lid->getProperty($veld));
					} catch (Exception $e) {
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

	public function viewLid(Lid $lid) {
		echo $lid->getNaamLink('leeg', 'visitekaartje');
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

	public function viewLid(Lid $lid) {

		foreach ($this->velden as $veld) {
			$return = '';
			switch ($veld) {
				case 'adres':
					$return.=$lid->getProperty('adres') . ';';
					$return.=$lid->getProperty('postcode') . ';';
					$return.=$lid->getProperty('woonplaats');
					break;
				case 'naam':
					$return.=$lid->getProperty('voornaam') . ';';
					$return.=$lid->getProperty('tussenvoegsel') . ';';
					$return.=$lid->getProperty('achternaam');
					break;
				case 'kring':
					$return.=$lid->getKring(false);
					break;
				case 'pasfoto':
					if (LidInstellingen::get('forum', 'naamWeergave') === 'Duckstad') {
						$return.=$this->getDuckfoto(false);
					} else {
						$return.=$lid->getPasfoto(false);
					}
					break;
				case 'patroon':
					$patroon = $lid->getPatroon();
					if ($patroon instanceof Lid) {
						$return.=$patroon->getNaamLink('full', 'plain');
					}
					break;
				case 'echtgenoot':
					$echtgenoot = $lid->getEchtgenoot();
					if ($echtgenoot instanceof Lid) {
						$return.=$echtgenoot->getNaamLink('full', 'plain');
					}
					break;
				case 'adresseringechtpaar':
					$return.=$lid->getAdresseringechtpaar();
					break;
				case 'verticale':
					$return.=$lid->getVerticale()->naam;
					break;
				case 'woonoord':
					$woonoord = $lid->getWoonoord();
					if ($woonoord instanceof OldGroep) {
						$return.=$woonoord->getNaam();
					}
				default:
					try {
						$return.=$lid->getProperty($veld);
					} catch (Exception $e) {
						//omit non-existant fields
					}
					break;
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
