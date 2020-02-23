<?php

namespace CsrDelft\view\lid;

use CsrDelft\model\security\LoginModel;
use CsrDelft\service\LidZoeker;
use CsrDelft\view\View;

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
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item active">Ledenlijst der Civitas</li></ul>';
	}

	public function getTitel() {
		return 'Ledenlijst der Civitas';
	}

	public function viewSelect($name, $options) {
		echo '<select class="form-control" name="' . $name . '" id="f' . $name . '">';
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
		echo '<div class="form-group">';
		echo '<label for="veldselectie">Veldselectie: </label>';
		echo '<div class="veldselectie">';
		$velden = $this->lidzoeker->getSelectableVelden();
		foreach ($velden as $key => $veld) {
			echo '<div class="form-check">';
			echo '<input class="form-check-input" type="checkbox" name="velden[]" id="veld' . $key . '" value="' . $key . '" ';
			if (in_array($key, $this->lidzoeker->getSelectedVelden())) {
				echo 'checked="checked" ';
			}
			echo ' />';
			echo '<label class="form-check-label" for="veld' . $key . '">' . ucfirst($veld) . '</label>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
	}

	public function view() {
		if ($this->lidzoeker->count() > 0) {
			if (strstr(REQUEST_URI, '?') !== false) {
				$url = REQUEST_URI . '&amp;addToGoogleContacts=true';
			} else {
				$url = REQUEST_URI . '?addToGoogleContacts=true';
			}
			echo '<a href="' . $url . '" class="btn float-right" title="Huidige selectie exporteren naar Google Contacts" onclick="return confirm(\'Weet u zeker dat u deze ' . $this->lidzoeker->count() . ' leden wilt importeren in uw Google-contacts?\')"><img src="/images/google.ico" width="16" height="16" alt="toevoegen aan Google contacts" /></a>';
		}
		echo getMelding();
		echo '<h1>' . (LoginModel::getProfiel()->isOudlid() ? 'Oud-leden en l' : 'L') . 'edenlijst </h1>';
		echo '<form id="zoekform" method="get">';
		echo '<div class="input-group">';
		echo '<input type="text" class="form-control" name="q" value="' . htmlspecialchars($this->lidzoeker->getQuery()) . '" /> ';
		echo '<div class="input-group-append"><button class="btn submit">Zoeken</button></div></div><a class="btn" id="toggleAdvanced" href="#geavanceerd">Geavanceerd</a>';

		echo '<div id="advanced" class="verborgen">';
		echo '<div class="form-group">';
		echo '<label for="status">Status:</label>';
		$this->viewSelect('status', array(
			'LEDEN' => 'Leden',
			'NOVIET' => 'Novieten',
			'GASTLID' => 'Gastlid',
			'OUDLEDEN' => 'Oudleden',
			'LEDEN|OUDLEDEN' => 'Leden & oudleden',
			'KRINGEL' => 'Kringel',
			'ALL' => 'Alles'
		));
		echo '</div>';
		echo '<div class="form-group">';
		echo '<label for="weergave">Weergave:</label>';
		$this->viewSelect('weergave', array(
			'lijst' => 'Lijst (standaard)',
			'kaartje' => 'Visitekaartjes',
			'csv' => 'CSV-bestand'));
		echo '</div>';

		//sorteren op:
		echo '<div class="form-group">';
		echo '<label for="sort">Sorteer op:</label>';
		$this->viewSelect('sort', $this->lidzoeker->getSortableVelden());
		echo '</div>';

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
