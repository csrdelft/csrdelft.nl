<?php
/**
 * GroepTabView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\entity\groepen\GroepTab;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;

abstract class GroepTabView extends GroepOmschrijvingView {

	protected abstract function getTabContent();

	public function getHtml() {
		$html = '<div id="groep-leden-' . $this->groep->id . '" class="groep-leden"><ul class="groep-tabs nobullets">';

		if (!$this->groep instanceof Verticale) {
			$html .= '<li class="geschiedenis float-left"><a class="btn" href="' . $this->groep->getUrl() . '" title="Bekijk geschiedenis"><span class="fa fa-clock-o"></span></a></li>';
		}

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepPasfotosView ? 'active' : '') . '" href="' . $this->groep->getUrl() . GroepTab::Pasfotos . '" title="' . GroepTab::getDescription(GroepTab::Pasfotos) . ' tonen"><span class="fa fa-user"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepLijstView ? 'active' : '') . '" href="' . $this->groep->getUrl() . GroepTab::Lijst . '" title="' . GroepTab::getDescription(GroepTab::Lijst) . ' tonen"><span class="fa fa-align-justify"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepStatistiekView ? 'active' : '') . '" href="' . $this->groep->getUrl() . GroepTab::Statistiek . '" title="' . GroepTab::getDescription(GroepTab::Statistiek) . ' tonen"><span class="fa fa-pie-chart"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEmailsView ? 'active' : '') . '" href="' . $this->groep->getUrl() . GroepTab::Emails . '" title="' . GroepTab::getDescription(GroepTab::Emails) . ' tonen"><span class="fa fa-envelope"></span></a></li>';

		$html .= '<li><a class="btn post noanim ' . ($this instanceof GroepEetwensView ? 'active' : '') . '" href="' . $this->groep->getUrl() . GroepTab::Eetwens . '" title="' . GroepTab::getDescription(GroepTab::Eetwens) . ' tonen"><span class="fa fa-heartbeat"></span></a></li>';

		$onclick = "$('#groep-" . $this->groep->id . "').toggleClass('leden-uitgeklapt');";
		$html .= '<li class="float-right"><a class="btn vergroot" id="groep-vergroot-' . $this->groep->id . '" data-vergroot="#groep-leden-content-' . $this->groep->id . '" title="Uitklappen" onclick="' . $onclick . '"><span class="fa fa-expand"></span></a>';

		$html .= '</ul><div id="groep-leden-content-' . $this->groep->id . '" class="groep-tab-content ' . $this->getType() . '">';

		$html .= '<ul id="groep-context-menu-' . $this->groep->id . '" class="dropdown-menu" role="menu"><li><a id="groep-lid-remove-' . $this->groep->id . '" tabindex="-1"><span class="fa fa-user-times"></span> &nbsp; Uit de ketzer halen</a></li></ul>';

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
		if ($this->groep->mag(AccessAction::Beheren)) {

			$this->javascript .= <<<JS

$('#groep-leden-content-{$this->groep->id} a.lidLink').contextMenu({
	menuSelector: "#groep-context-menu-{$this->groep->id}",
	menuSelected: function (invokedOn, selectedMenu) {
		var a = $(invokedOn).closest('a.lidLink');
		if (confirm('Weet u zeker dat u ' + a.attr('title') + ' uit de ketzer wilt halen?')) {
			var url = a.attr('href').replace('/profiel/', 'afmelden/');
			$.post('{$this->groep->getUrl()}' + url, {}, window.context.domUpdate);
		}
	}
});
JS;
		}
		$html .= $this->getScriptTag();

		$html .= '</div>';

		if (property_exists($this->groep, 'aanmeld_limiet') AND isset($this->groep->aanmeld_limiet)) {
			// Progress bar
			$aantal = $this->groep->aantalLeden();
			$percent = round($aantal * 100 / $this->groep->aanmeld_limiet);
			// Aanmelden mogelijk?
			if (time() > strtotime($this->groep->aanmelden_vanaf) AND time() < strtotime($this->groep->aanmelden_tot)) {
				$verschil = $this->groep->aanmeld_limiet - $aantal;
				if ($verschil === 0) {
					$title = 'Inschrijvingen vol!';
					$color = ' progress-bar-info';
				} else {
					$title = 'Inschrijvingen geopend! Nog ' . $verschil . ' plek' . ($verschil === 1 ? '' : 'ken') . ' vrij.';
					$color = ' progress-bar-success';
				}
			} // Bewerken mogelijk?
			elseif ($this->groep->getLid(LoginModel::getUid()) AND time() < strtotime($this->groep->bewerken_tot)) {
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
