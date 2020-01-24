<?php

namespace CsrDelft\view\lid;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;

/**
 * CSV in een textarea met clientside downloadknop
 */
class LLCSV extends LLWeergave {

	public function viewHeader() {
		echo '<textarea class="csv">';
		foreach ($this->velden as $veld) {
			switch ($veld) {

				case 'adres':
					echo 'adres;';
					echo 'postcode;';
					echo 'woonplaats;';
					break;

				case 'naam':
					echo 'voornaam;';
					echo 'tussenvoegsel;';
					echo 'achternaam;';
					break;

				default:
					echo $veld . ';';
			}
		}

		echo "\n";
	}

	public function viewFooter() {
		echo '</textarea>';
		?>
		<a href="" class="btn btn-primary download-ledenlijst">Download</a>
		<script>
			let csvContent = "data:text/csv;charset=utf-8,";
			csvContent += $('textarea.csv').text();
			let encodedUri = encodeURI(csvContent);
			let link = $('.download-ledenlijst');
			link.attr("href", encodedUri);
			link.attr("download", "ledenlijst.csv");
		</script>
		<?php
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
					$return .= $profiel->getPasfotoPath();
					break;

				case 'patroon':
					$patroon = ProfielRepository::get($profiel->patroon);
					if ($patroon) {
						$return .= $patroon->getNaam('volledig');
					}
					break;

				case 'echtgenoot':
					$echtgenoot = ProfielRepository::get($profiel->echtgenoot);
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
