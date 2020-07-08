<?php

namespace CsrDelft\view\lid;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use DateTimeInterface;
use Exception;

/**
 * De 'normale' ledenlijst, zoals het is zoals het was.
 */
class LLLijst extends LLWeergave {

	private function viewVeldnamen() {
		echo '<tr>';
		foreach ($this->velden as $veld) {
			echo '<th>' . ucfirst($veld) . '</th>';
		}
		echo '</tr>';
	}

	public function viewHeader() {
		echo '<table class="zoekResultaat ctx-offline-datatable"
						id="zoekResultaat" data-display-length="50" data-length-change="false">';
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
				case 'woonoord':
					$aoColumns[] = '{"sType": \'html\'}';
					break;
				default:
					$aoColumns[] = 'null';
			}
		}
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
					$patroon = ProfielRepository::get($profiel->patroon);
					if ($patroon) {
						echo $patroon->getLink('volledig');
					} else {
						echo '-';
					}
					break;

				case 'echtgenoot':
					$echtgenoot = ProfielRepository::get($profiel->echtgenoot);
					if ($echtgenoot) {
						echo $echtgenoot->getLink('volledig');
					} else {
						echo '-';
					}
					break;

				case 'status':
					echo LidStatus::from($profiel->status)->getDescription();
					break;

				case 'verticale':
					if ($profiel->getVerticale())
						echo htmlspecialchars($profiel->getVerticale()->naam);
					break;

				case 'woonoord':
					$woonoord = $profiel->getWoonoord();
					if ($woonoord) {
						echo '<a href="' . $woonoord->getUrl() . '">' . htmlspecialchars($woonoord->naam) . '</a>';
					}
					break;

				case 'linkedin':
				case 'website':
					echo '<a target="_blank" href="' . htmlspecialchars($profiel->$veld) . '">' . htmlspecialchars($profiel->$veld) . '</a>';
					break;

				default:
					try {
						if ($profiel->$veld instanceof DateTimeInterface) {
							echo date_format_intl($profiel->$veld, DATE_FORMAT);
						} else {
							echo htmlspecialchars($profiel->$veld);
						}
					} catch (Exception $e) {
						echo ' - ';
					}
			}
			echo '</td>';
		}

		echo '</tr>';
	}

}
