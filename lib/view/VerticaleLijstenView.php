<?php

namespace CsrDelft\view;

use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\ProfielModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class VerticaleLijstenView implements View {

	public function view() {
		echo '<p>Gebruik deze lijstjes om de maillijsten opnieuw in te stellen.</p>';

		echo '<table class="table"><tr>';
		$verticalen = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');
		foreach ($verticalen as $letter) {
			$result = ProfielModel::instance()->find('verticale = ? AND (status="S_LID" OR status="S_NOVIET" OR status="S_GASTLID" OR status="S_KRINGEL")', [$letter]);
			if ($result !== false) {
				echo '<td><h3>Verticale ' . VerticalenModel::get($letter)->naam . '</h3><pre onclick="let range = document.createRange(); range.selectNode(this); window.getSelection().addRange(range)">';

				foreach ($result as $profiel) {
					echo $profiel->uid . "@csrdelft.nl\n";
				}

				echo '</pre></td>';
			}
		}
		echo '</tr></table>';
	}

	public function getTitel() {
		return 'Verticalelijsten';
	}

	public function getBreadcrumbs() {
		// TODO: Implement getBreadcrumbs() method.
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		// TODO: Implement getModel() method.
	}
}
