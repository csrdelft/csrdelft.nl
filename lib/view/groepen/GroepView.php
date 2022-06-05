<?php
/**
 * GroepView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepTab;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\GroepRepository;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\groepen\leden\GroepEetwensView;
use CsrDelft\view\groepen\leden\GroepEmailsView;
use CsrDelft\view\groepen\leden\GroepLijstView;
use CsrDelft\view\groepen\leden\GroepPasfotosView;
use CsrDelft\view\groepen\leden\GroepStatistiekView;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;

class GroepView implements FormElement, ToResponse
{
	use ToHtmlResponse;
	private $groep;
	private $leden;
	private $geschiedenis;
	private $bbAan;

	public function __construct(
		Groep $groep,
		$tab = null,
		$geschiedenis = false,
		$bbAan = false
	) {
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
				/** @var GroepRepository $repository */
				$repository = ContainerFacade::getContainer()
					->get('doctrine.orm.entity_manager')
					->getRepository(get_class($groep));
				$statistiek = $repository->getStatistieken($groep);
				$this->leden = new GroepStatistiekView($groep, $statistiek);
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

	public function getModel()
	{
		return $this->groep;
	}

	public function getTitel()
	{
		return $this->groep->naam;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getHtml()
	{
		$html =
			'<a id="a-' .
			$this->groep->id .
			'" name="' .
			$this->groep->id .
			'"></a><div id="groep-' .
			$this->groep->id .
			'" class="bb-groep';
		if ($this->geschiedenis) {
			$html .= ' state-geschiedenis';
		}
		if ($this->bbAan) {
			$html .= ' bb-block';
		}
		$html .=
			'"><div id="groep-samenvatting-' .
			$this->groep->id .
			'" class="groep-samenvatting">';
		if ($this->groep->mag(AccessAction::Wijzigen())) {
			$html .=
				'<div class="float-end"><a class="btn" href="' .
				$this->groep->getUrl() .
				'/wijzigen' .
				'" title="Wijzig ' .
				htmlspecialchars($this->groep->naam) .
				'"><i class="fas fa-pencil"></i></a></div>';
		}
		$html .= '<h3>' . $this->getTitel();
		if (
			property_exists($this->groep, 'locatie') and !empty($this->groep->locatie)
		) {
			$html .=
				' &nbsp; <a target="_blank" href="https://maps.google.nl/maps?q=' .
				urlencode($this->groep->locatie) .
				'" title="' .
				$this->groep->locatie .
				'" class="lichtgrijs fas fa-map-marker fa-lg"></a>';
		}
		$html .= '</h3>';
		$html .= CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			$html .=
				'<div class="clear">&nbsp;</div><a id="groep-omschrijving-' .
				$this->groep->id .
				'" class="post noanim" href="' .
				$this->groep->getUrl() .
				'/omschrijving">Meer lezen »</a>';
		}
		$html .= '</div>';
		$html .= $this->leden->getHtml();
		$html .= '<div class="clear">&nbsp</div></div>';
		return $html;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function getJavascript()
	{
		return null;
	}

	public function getType()
	{
		return classNameZonderNamespace(get_class($this->groep));
	}
}
