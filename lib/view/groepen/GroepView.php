<?php
/**
 * GroepView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepTab;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\groepen\leden\GroepEetwensView;
use CsrDelft\view\groepen\leden\GroepEmailsView;
use CsrDelft\view\groepen\leden\GroepLijstView;
use CsrDelft\view\groepen\leden\GroepPasfotosView;
use CsrDelft\view\groepen\leden\GroepStatistiekView;
use function CsrDelft\classNameZonderNamespace;

class GroepView implements FormElement {

	private $groep;
	private $leden;
	private $geschiedenis;
	private $bbAan;

	public function __construct(AbstractGroep $groep, $tab = null, $geschiedenis = false, $bbAan = false) {
		$this->groep = $groep;
		$this->geschiedenis = $geschiedenis;
		$this->bbAan = $bbAan;
		switch ($tab) {

			case GroepTab::Pasfotos:
				$this->leden = new GroepPasfotosView($groep);
				break;

			case GroepTab::Lijst:
				$this->leden = new GroepLijstView($groep);
				break;

			case GroepTab::Statistiek:
				$this->leden = new GroepStatistiekView($groep);
				break;

			case GroepTab::Emails:
				$this->leden = new GroepEmailsView($groep);
				break;

			case GroepTab::Eetwens:
				$this->leden = new GroepEetwensView($groep);
				break;

			default:
				if ($groep->keuzelijst) {
					$this->leden = new GroepLijstView($groep);
				} else {
					$this->leden = new GroepPasfotosView($groep);
				}
		}
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getHtml() {
		$html = '<a name="' . $this->groep->id . '"></a><div id="groep-' . $this->groep->id . '" class="bb-groep';
		if ($this->geschiedenis) {
			$html .= ' state-geschiedenis';
		}
		if ($this->bbAan) {
			$html .= ' bb-block';
		}
		$html .= '"><div id="groep-samenvatting-' . $this->groep->id . '" class="groep-samenvatting">';
		if ($this->groep->mag(AccessAction::Wijzigen)) {
			$html .= '<div class="float-right"><a class="btn" href="' . $this->groep->getUrl() . 'wijzigen' . '" title="Wijzig ' . htmlspecialchars($this->groep->naam) . '"><span class="fa fa-pencil"></span></a></div>';
		}
		$html .= '<h3>' . $this->getTitel();
		if (property_exists($this->groep, 'locatie') AND !empty($this->groep->locatie)) {
			$html .= ' &nbsp; <a target="_blank" href="https://maps.google.nl/maps?q=' . urlencode($this->groep->locatie) . '" title="' . $this->groep->locatie . '" class="lichtgrijs fa fa-map-marker fa-lg"></a>';
		}
		$html .= ' <span class="groep-id-hint">(<a href="' . $this->groep->getUrl() . '">#' . $this->groep->id . '</a>)</span>';
		$html .= '</h3>';
		$html .= CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			$html .= '<div class="clear">&nbsp;</div><a id="groep-omschrijving-' . $this->groep->id . '" class="post noanim" href="' . $this->groep->getUrl() . 'omschrijving">Meer lezen »</a>';
		}
		$html .= '</div>';
		$html .= $this->leden->getHtml();
		$html .= '<div class="clear">&nbsp</div></div>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getJavascript() {
		return null;
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this->groep));
	}

}
