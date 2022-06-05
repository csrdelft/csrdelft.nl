<?php

namespace CsrDelft\view\lid;

use CsrDelft\service\LidZoekerService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
class LedenlijstContent implements View
{
	use ToHtmlResponse;

	/**
	 * Lid-zoeker
	 * @var LidZoekerService
	 */
	private $lidzoeker;
	/**
	 * @var Request
	 */
	private $requestStack;

	public function __construct(Request $requestStack, LidZoekerService $zoeker)
	{
		$this->lidzoeker = $zoeker;
		$this->requestStack = $requestStack;
	}

	public function getModel()
	{
		return $this->lidzoeker;
	}

	public function getBreadcrumbs()
	{
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>' .
			'<li class="breadcrumb-item active">Ledenlijst der Civitas</li></ul>';
	}

	public function getTitel()
	{
		return 'Ledenlijst der Civitas';
	}

	public function viewSelect($name, $options)
	{
		$html = '';
		$html .=
			'<select class="form-select" name="' . $name . '" id="f' . $name . '">';
		foreach ($options as $key => $value) {
			$html .= '<option value="' . htmlspecialchars($key) . '"';
			if ($key == $this->lidzoeker->getRawQuery($name)) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . htmlspecialchars($value) . '</option>';
		}
		$html .= '</select> ';
		return $html;
	}

	public function viewVeldselectie()
	{
		$html = '';
		$html .= '<div class="mb-3">';
		$html .= '<label for="veldselectie">Veldselectie: </label>';
		$html .= '<div class="veldselectie">';
		$velden = $this->lidzoeker->getSelectableVelden();
		foreach ($velden as $key => $veld) {
			$html .= '<div class="form-check">';
			$html .=
				'<input class="form-check-input" type="checkbox" name="velden[]" id="veld' .
				$key .
				'" value="' .
				$key .
				'" ';
			if (in_array($key, $this->lidzoeker->getSelectedVelden())) {
				$html .= 'checked="checked" ';
			}
			$html .= ' />';
			$html .=
				'<label class="form-check-label" for="veld' .
				$key .
				'">' .
				ucfirst($veld) .
				'</label>';
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	public function __toString()
	{
		$html = '';
		$requestUri = $this->requestStack->getRequestUri();
		if ($this->lidzoeker->count() > 0) {
			if (strstr($requestUri, '?') !== false) {
				$url = $requestUri . '&addToGoogleContacts=true';
			} else {
				$url = $requestUri . '?addToGoogleContacts=true';
			}
			$html .=
				'<a href="' .
				htmlspecialchars($url) .
				'" class="btn float-end" title="Huidige selectie exporteren naar Google Contacts" onclick="return confirm(\'Weet u zeker dat u deze ' .
				$this->lidzoeker->count() .
				' leden wilt importeren in uw Google-contacts?\')"><img src="/images/google.ico" width="16" height="16" alt="toevoegen aan Google contacts" /></a>';
			if (strstr($requestUri, '?') !== false) {
				$url = $requestUri . '&exportVcf=true';
			} else {
				$url = $requestUri . '?exportVcf=true';
			}
			$html .=
				'<a href="' .
				htmlspecialchars($url) .
				'" class="btn float-end" title="Huidige selectie exporteren als vcard">' .
				Icon::getTag('vcard_add') .
				'</a>';
		}
		$html .= getMelding();
		$html .=
			'<h1>' .
			(LoginService::getProfiel()->isOudlid() ? 'Oud-leden en l' : 'L') .
			'edenlijst </h1>';
		$html .= '<form id="zoekform" method="get">';
		$html .= '<div class="input-group">';
		$html .=
			'<input type="text" class="form-control" name="q" value="' .
			htmlspecialchars($this->lidzoeker->getQuery()) .
			'" /> ';
		$html .=
			'<div class="input-group-text"><button class="btn submit">Zoeken</button></div></div><a class="btn" id="toggleAdvanced" href="#geavanceerd">Geavanceerd</a>';

		$html .= '<div id="advanced" class="verborgen">';
		$html .= '<div class="mb-3">';
		$html .= '<label for="status">Status:</label>';
		$html .= $this->viewSelect('status', [
			'LEDEN' => 'Leden',
			'NOVIET' => 'Novieten',
			'GASTLID' => 'Gastlid',
			'OUDLEDEN' => 'Oudleden',
			'LEDEN|OUDLEDEN' => 'Leden & oudleden',
			'KRINGEL' => 'Kringel',
			'ALL' => 'Alles',
		]);
		$html .= '</div>';
		$html .= '<div class="mb-3">';
		$html .= '<label for="weergave">Weergave:</label>';
		$html .= $this->viewSelect('weergave', [
			'lijst' => 'Lijst (standaard)',
			'kaartje' => 'Visitekaartjes',
			'csv' => 'CSV-bestand',
		]);
		$html .= '</div>';

		//sorteren op:
		$html .= '<div class="mb-3">';
		$html .= '<label for="sort">Sorteer op:</label>';
		$html .= $this->viewSelect('sort', $this->lidzoeker->getSortableVelden());
		$html .= '</div>';

		//selecteer velden
		$html .= '<div id="veldselectiecontainer">';
		$html .= $this->viewVeldselectie();
		$html .= '</div><br />';

		$html .= '</div>'; //einde advanced div.
		$html .= '</form>';

		$html .= '<hr class="clear" />';

		if ($this->lidzoeker->count() > 0) {
			$class = $this->lidzoeker->getWeergave();
			/** @var LLWeergave $weergave */
			$weergave = new $class($this->lidzoeker);
			$html .= $weergave->__toString();
		} elseif ($this->lidzoeker->searched()) {
			$html .= 'Geen resultaten';
		} else {
			//nog niet gezocht.
		}
		$html .= <<<HTML
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
HTML;
		return $html;
	}
}
