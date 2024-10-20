<?php
/**
 * GroepView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepTab;
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
use CsrDelft\view\Icon;
use Twig\Environment;

class GroepView implements FormElement, ToResponse
{
	use ToHtmlResponse;
	private $leden;

	public function __construct(
		Environment $twig,
		private Groep $groep,
		$tab = null,
		private $geschiedenis = false,
		private $bbAan = false
	) {
		switch ($tab) {
			case GroepTab::Pasfotos:
				$this->leden = new GroepPasfotosView($twig, $this->groep);
				break;

			case GroepTab::Lijst:
				$this->leden = new GroepLijstView($twig, $this->groep);
				break;

			case GroepTab::Statistiek:
				/** @var GroepRepository $repository */
				$repository = ContainerFacade::getContainer()
					->get('doctrine.orm.entity_manager')
					->getRepository($this->groep::class);
				$statistiek = $repository->getStatistieken($this->groep);
				$this->leden = new GroepStatistiekView($twig, $this->groep, $statistiek);
				break;

			case GroepTab::Emails:
				$this->leden = new GroepEmailsView($twig, $this->groep);
				break;

			case GroepTab::Eetwens:
				$this->leden = new GroepEetwensView($twig, $this->groep);
				break;

			default:
				if ($this->groep->keuzelijst) {
					$this->leden = new GroepLijstView($twig, $this->groep);
				} else {
					$this->leden = new GroepPasfotosView($twig, $this->groep);
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
		$security = ContainerFacade::getContainer()->get('security');
		if ($security->isGranted(AbstractGroepVoter::WIJZIGEN, $this->groep)) {
			$html .=
				'<div class="float-end"><a class="btn" href="' .
				$this->groep->getUrl() .
				'/wijzigen' .
				'" title="Wijzig ' .
				htmlspecialchars($this->groep->naam) .
				'">' .
				Icon::getTag('bewerken') .
				'</a></div>';
		}
		$html .= '<h3>' . $this->getTitel();
		if (
			property_exists($this->groep, 'locatie') && !empty($this->groep->locatie)
		) {
			$html .=
				' &nbsp; <a target="_blank" href="https://maps.google.nl/maps?q=' .
				urlencode($this->groep->locatie) .
				'" title="' .
				$this->groep->locatie .
				'" class="lichtgrijs not-external">' .
				Icon::getTag('adres', null, $this->groep->locatie, 'fa-lg') .
				'</a>';
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
		$html .= $this->leden->__toString();
		$html .= '<div class="clear">&nbsp</div></div>';
		return $html;
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}

	public function getJavascript()
	{
		return null;
	}

	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace($this->groep::class);
	}
}
