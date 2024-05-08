<?php

namespace CsrDelft\view\lid;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use Exception;

/**
 * CSV in een textarea met clientside downloadknop
 */
class LLCSV extends LLWeergave
{
	public function viewHeader()
	{
		$html = '';
		$html .= '<textarea class="csv">';
		foreach ($this->velden as $veld) {
			switch ($veld) {
				case 'adres':
					$html .= 'adres;';
					$html .= 'postcode;';
					$html .= 'woonplaats;';
					$html .= 'land;';
					break;

				case 'adres_ouders':
					$html .= 'adres_ouders;';
					$html .= 'postcode_ouders;';
					$html .= 'woonplaats_ouders;';
					$html .= 'land_ouders;';
					break;

				case 'naam':
					$html .= 'voornaam;';
					$html .= 'tussenvoegsel;';
					$html .= 'achternaam;';
					break;

				default:
					$html .= $veld . ';';
			}
		}

		$html .= "\n";
		return $html;
	}

	public function viewFooter(): string
	{
		$html = '';
		$html .= '</textarea>';
		$html .= <<<HTML
<a href="" class="btn btn-primary download-ledenlijst">Download</a>
<script>
	let csvContent = "data:text/csv;charset=utf-8,";
	csvContent += $('textarea.csv').text();
	let encodedUri = encodeURI(csvContent);
	let link = $('.download-ledenlijst');
	link.attr("href", encodedUri);
	link.attr("download", "ledenlijst.csv");
</script>
HTML;
		return $html;
	}

	public function viewLid(Profiel $profiel): string
	{
		$html = '';

		foreach ($this->velden as $veld) {
			$return = '';
			switch ($veld) {
				case 'adres':
					$return .= str_replace('#', '', $profiel->adres) . ';';
					$return .= $profiel->postcode . ';';
					$return .= $profiel->woonplaats . ';';
					$return .= $profiel->land;
					break;

				case 'adres_ouders':
					$return .= str_replace('#', '', $profiel->o_adres) . ';';
					$return .= $profiel->o_postcode . ';';
					$return .= $profiel->o_woonplaats . ';';
					$return .= $profiel->o_land;
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

				case 'geslacht':
					if ($profiel->geslacht) {
						$return .= $profiel->geslacht->getValue();
					}
					break;

				case 'gebdatum':
					if ($profiel->gebdatum) {
						$return .= DateUtil::dateFormatIntl(
							$profiel->gebdatum,
							DateUtil::DATE_FORMAT
						);
					}
					break;

				default:
					try {
						$return .= $profiel->$veld;
					} catch (Exception $e) {
						//omit non-existant fields
					}
			}
			$html .= htmlspecialchars($return) . ';';
		}
		$html .= "\n";
		return $html;
	}
}
