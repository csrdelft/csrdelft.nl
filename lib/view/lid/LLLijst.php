<?php

namespace CsrDelft\view\lid;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use DateTimeInterface;
use Exception;

/**
 * De 'normale' ledenlijst, zoals het is zoals het was.
 */
class LLLijst extends LLWeergave
{
	private function viewVeldnamen()
	{
		$html = '';
		$html .= '<tr>';
		foreach ($this->velden as $veld) {
			$html .= '<th>' . ucfirst((string) $veld) . '</th>';
		}
		$html .= '</tr>';
		return $html;
	}

	public function viewHeader()
	{
		$html = '';
		$html .= '<table class="zoekResultaat ctx-offline-datatable"
						id="zoekResultaat" data-display-length="50" data-length-change="false">';
		$html .= '<thead>';
		$html .= $this->viewVeldnamen();
		$html .= '</thead><tbody>';
		return $html;
	}

	public function viewFooter()
	{
		$html = '';
		$html .= "</tbody>\n<tfoot>";
		$html .= $this->viewVeldnamen();
		$html .= '</tfoot></table>';

		//fix jQuery datatables op deze tabel.
		$aoColumns = [];
		foreach ($this->velden as $veld) {
			$aoColumns[] = match ($veld) {
				'pasfoto' => '{"bSortable": false}',
				'email',
				'naam',
				'kring',
				'patroon',
				'verticale',
				'woonoord'
					=> '{"sType": \'html\'}',
				default => 'null',
			};
		}
		return $html;
	}

	public function viewLid(Profiel $profiel)
	{
		$html = '';
		$html .= '<tr id="lid' . $profiel->uid . '">';
		foreach ($this->velden as $veld) {
			$html .= '<td class="' . $veld . '">';
			switch ($veld) {
				case 'email':
					$email = $profiel->getPrimaryEmail();
					if ($email) {
						$html .= '<a href="mailto:' . $email . '">' . $email . '</a>';
					}
					break;

				case 'adres':
					$html .= htmlspecialchars((string) $profiel->getAdres());
					break;

				case 'adres_ouders':
					$html .= htmlspecialchars((string) $profiel->getAdresOuders());
					break;

				case 'kring':
					$kring = $profiel->getKring();
					if ($kring) {
						$html .=
							'<a href="' . $kring->getUrl() . '">' . $kring->naam . '</a>';
					}
					break;

				case 'naam':
					//we stoppen er een verborgen <span> bij waar op gesorteerd wordt door datatables.
					$html .=
						'<span class="verborgen">' .
						$profiel->getNaam('streeplijst') .
						'</span>';
					$html .= $profiel->getLink('volledig');
					break;

				case 'pasfoto':
					$html .= $profiel->getPasfotoTag();
					break;

				case 'patroon':
					$patroon = ProfielRepository::get($profiel->patroon);
					if ($patroon) {
						$html .= $patroon->getLink('volledig');
					} else {
						$html .= '-';
					}
					break;

				case 'echtgenoot':
					$echtgenoot = ProfielRepository::get($profiel->echtgenoot);
					if ($echtgenoot) {
						$html .= $echtgenoot->getLink('volledig');
					} else {
						$html .= '-';
					}
					break;

				case 'status':
					$html .= LidStatus::from($profiel->status)->getDescription();
					break;

				case 'verticale':
					if ($profiel->getVerticale()) {
						$html .= htmlspecialchars($profiel->getVerticale()->naam);
					}
					break;

				case 'woonoord':
					$woonoord = $profiel->getWoonoord();
					if ($woonoord) {
						$html .=
							'<a href="' .
							$woonoord->getUrl() .
							'">' .
							htmlspecialchars($woonoord->naam) .
							'</a>';
					}
					break;

				case 'linkedin':
				case 'website':
					$html .=
						'<a target="_blank" href="' .
						htmlspecialchars((string) $profiel->$veld) .
						'">' .
						htmlspecialchars((string) $profiel->$veld) .
						'</a>';
					break;

				case 'geslacht':
					if ($profiel->geslacht) {
						$html .= htmlspecialchars((string) $profiel->geslacht->getValue());
					}
					break;

				default:
					try {
						if ($profiel->$veld instanceof DateTimeInterface) {
							$html .= DateUtil::dateFormatIntl(
								$profiel->$veld,
								DateUtil::DATE_FORMAT
							);
						} else {
							$html .= htmlspecialchars((string) $profiel->$veld);
						}
					} catch (Exception) {
						$html .= ' - ';
					}
			}
			$html .= '</td>';
		}

		$html .= '</tr>';
		return $html;
	}
}
