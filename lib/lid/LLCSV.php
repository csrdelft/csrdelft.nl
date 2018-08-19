<?php

namespace CsrDelft\lid;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;

/**
 * CSV in een textarea.
 * Eventueel zou het nog geforceerd downloadbaar gemaakt kunnen worden
 */
class LLCSV extends LLWeergave {

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