<?php
require_once 'model/VerjaardagenModel.class.php';

/**
 * VerjaardagenView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * Weergeven van verjaardagen.
 */
class VerjaardagenView implements View {

	private $model;

	public function __construct($model) {
		$this->model = $model;
	}

	function getTitel() {
		return 'Verjaardagen';
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	function getModel() {
		return $this->model;
	}

	function view() {
		switch ($this->model) {
			case 'alleverjaardagen':
				?>
				<ul class="horizontal nobullets">
					<li>
						<a href="/ledenlijst">Ledenlijst</a>
					</li>
					<li class="active">
						<a href="/leden/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
					</li>
					<li>
						<a href="/verticalen">Kringen</a>
					</li>
				</ul>
				<hr />
				<h1>Verjaardagen</h1>
				<div class="verjaardagen">
					<?php
					# de verjaardagen die vandaag zijn krijgen een highlight
					$nu = time();
					$dezemaand = date('n', $nu);
					$dezedag = date('j', $nu);

					$maanden = array(
						1	 => 'Januari',
						2	 => 'Februari',
						3	 => 'Maart',
						4	 => 'April',
						5	 => 'Mei',
						6	 => 'Juni',
						7	 => 'Juli',
						8	 => 'Augustus',
						9	 => 'September',
						10	 => 'Oktober',
						11	 => 'November',
						12	 => 'December',
					);

					for ($m = 0; $m < 12; $m++) {
						$maand = ($dezemaand - 1 + $m) % 12 + 1;
						echo '<table class="inline"><tr><th></th><th><h3>' . $maanden[$maand] . '</h3></th></tr>';
						$verjaardagen = VerjaardagenModel::get($maand);
						foreach ($verjaardagen as $verjaardag) {
							echo '<tr>';
							$profiel = ProfielModel::get($verjaardag['uid']);
							if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand) {
								echo '<td class="text-right dikgedrukt cursief">';
							} else {
								echo '<td class="text-right">';
							}
							echo $verjaardag['gebdag'];
							echo '</td>';
							if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand) {
								echo '<td class="dikgedrukt cursief">&nbsp; ';
							} else {
								echo '<td>&nbsp; ';
							}
							echo $profiel->getLink('civitas');
							echo '</td>';
							echo '</tr>';
						}
						echo '</table>';
					}
					echo '</div>';
					break;
				case 'komende':
					if (LoginModel::mag('P_LEDEN_READ')) {
						$toonpasfotos = LidInstellingen::get('zijbalk', 'verjaardagen_pasfotos') == 'ja';
					} else {
						$toonpasfotos = false;
					}

					echo '<div id="zijbalk_verjaardagen"><div class="zijbalk-kopje">';
					if (LoginModel::mag('P_LEDEN_READ')) {
						echo '<a href="/leden/verjaardagen">Verjaardagen</a>';
					} else {
						echo 'Verjaardagen';
					}
					echo '</div>';

					$aVerjaardagen = VerjaardagenModel::getKomende((int) LidInstellingen::get('zijbalk', 'verjaardagen'));

					if ($toonpasfotos) {
						echo '<div class="item" id="komende_pasfotos">';
						foreach ($aVerjaardagen as $profiel) {
							echo '<div class="verjaardag';
							if ($profiel->isJarig()) {
								echo ' cursief';
							}
							echo '">';
							echo $profiel->getLink('pasfoto');
							echo '<span class="datum">' . date('d-m', strtotime($profiel->gebdatum)) . '</span>';
							echo '</div>';
						}
						echo '<div class="clear"></div></div>';
					} else {
						foreach ($aVerjaardagen as $profiel) {
							echo '<div class="item">' . date('d-m', strtotime($profiel->gebdatum)) . ' ';
							if ($profiel->isJarig()) {
								echo '<span class="cursief">';
							}
							echo $profiel->getLink('civitas');
							if ($profiel->isJarig()) {
								echo '</span>';
							}
							echo '</div>';
						}
					}
					echo '</div>'; //einde wrapperdiv
					break;
			}
		}

	}
	