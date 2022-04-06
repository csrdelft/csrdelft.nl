<?php
/**
 * GroepTabView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\groepen\enum\GroepTab;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;

abstract class GroepTabView extends GroepOmschrijvingView {

	protected abstract function getTabContent();

	public function getHtml() {
		$html = '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		if (!$this->groep instanceof Verticale) {
			$html .= '<li class="geschiedenis"><a class="btn" href="' . $this->groep->getUrl() . '" title="Bekijk geschiedenis"><span class="fas fa-clock"></span></a></li>';
		}

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'btn-primary' : '') . '" href="' . $this->groep->getUrl() . '/' . GroepTab::Pasfotos . '" title="' . GroepTab::Pasfotos()->getDescription() . ' tonen"><span class="fas fa-user"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'btn-primary' : '') . '" href="' . $this->groep->getUrl() . '/' . GroepTab::Lijst . '" title="' . GroepTab::Lijst()->getDescription() . ' tonen"><span class="fas fa-list"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'btn-primary' : '') . '" href="' . $this->groep->getUrl() . '/' . GroepTab::Statistiek . '" title="' . GroepTab::Statistiek()->getDescription() . ' tonen"><span class="fas fa-chart-pie"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'btn-primary' : '') . '" href="' . $this->groep->getUrl() . '/' . GroepTab::Emails . '" title="' . GroepTab::Emails()->getDescription() . ' tonen"><span class="fas fa-envelope"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEetwensView ? 'btn-primary' : '') . '" href="' . $this->groep->getUrl() . '/' . GroepTab::Eetwens . '" title="' . GroepTab::Eetwens()->getDescription() . ' tonen"><span class="fas fa-heartbeat"></span></a></li>';

		$onclick = "$('#groep-" . $this->groep->id . "').toggleClass('leden-uitgeklapt');";
		$html .= '<li class="knop-vergroot"><a class="btn vergroot" id="groep-vergroot-' . $this->groep->id . '" data-vergroot="#groep-leden-content-' . $this->groep->id . '" title="Uitklappen" onclick="' . $onclick . '"><span class="fas fa-expand"></span></a>';

		$html .= '</ul><div id="groep-leden-content-' . $this->groep->id . '" class="groep-tab-content ' . $this->getType() . '">';

		$html .= '<ul id="groep-context-menu-' . $this->groep->id . '" class="dropdown-menu" role="menu"><li><a id="groep-lid-remove-' . $this->groep->id . '" tabindex="-1"><span class="fas fa-user-times"></span> &nbsp; Uit de ketzer halen</a></li></ul>';

		$html .= $this->getTabContent();

		$this->javascript .= <<<JS

var tabContent = $('#groep-leden-content-{$this->groep->id}');
var availableHeight = tabContent.parent().parent().height() - tabContent.prev('ul.groep-tabs').height();
if ($('#groep-{$this->groep->id}').hasClass('leden-uitgeklapt')) {
	tabContent.height(tabContent.prop('scrollHeight') + 1);
	var knop = $('#groep-vergroot-{$this->groep->id}');
	knop.attr('title', 'Inklappen');
	knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
	knop.attr('data-vergroot-oud', availableHeight);
}
else {
	tabContent.height(availableHeight);
}
JS;
		if ($this->groep->mag(AccessAction::Beheren())) {

			$this->javascript .= <<<JS

$('#groep-leden-content-{$this->groep->id} a.lidLink').contextMenu({
	menuSelector: "#groep-context-menu-{$this->groep->id}",
	menuSelected: function (invokedOn, selectedMenu) {
		var a = $(invokedOn).closest('a.lidLink');
		if (confirm('Weet u zeker dat u ' + a.attr('title') + ' uit de ketzer wilt halen?')) {
			$.post('{$this->groep->getUrl()}' + '/ketzer/afmelden/' + a.data('lid'), {}, window.context.domUpdate);
		}
	}
});
JS;
		}
		$html .= $this->getScriptTag();

		$html .= '</div>';

		$nu = date_create_immutable();

		if ($this->groep instanceof HeeftAanmeldLimiet AND $this->groep->getAanmeldLimiet() != null) {
			// Progress bar
			$aantal = $this->groep->aantalLeden();
			$percent = round($aantal * 100 / $this->groep->getAanmeldLimiet());
			// Aanmelden mogelijk?
			if ($nu > $this->groep->aanmeldenVanaf && $nu < $this->groep->aanmeldenTot) {
				$verschil = $this->groep->getAanmeldLimiet() - $aantal;
				if ($verschil === 0) {
					$title = 'Inschrijvingen vol!';
					$color = ' progress-bar-info';
				} else {
					$title = 'Inschrijvingen geopend! Nog ' . $verschil . ' plek' . ($verschil === 1 ? '' : 'ken') . ' vrij.';
					$color = ' progress-bar-success';
				}
			} // Bewerken mogelijk?
			elseif ($this->groep->getLid(LoginService::getUid()) && date_create_immutable() < $this->groep->bewerkenTot) {
				$title = 'Inschrijvingen gesloten! Inschrijving bewerken is nog wel toegestaan.';
				$color = ' progress-bar-warning';
			} else {
				$title = 'Inschrijvingen gesloten!';
				$color = ' progress-bar-info';
			}
			$html .= '<br /><div class="progress" title="' . $title . '"><div class="progress-bar' . $color . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">' . $percent . '%</div></div>';
		}
		return $html . '</div>';
	}

}
