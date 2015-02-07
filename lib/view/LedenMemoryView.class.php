<?php

/**
 * LedenMemoryView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het spelletje memory met pasfotos en namen van leden
 */
class LedenMemoryView extends CompressedLayout {

	private $leden = array();
	private $learnmode;
	private $cheat;

	public function __construct() {
		$lidstatus = array_merge(LidStatus::$lidlike, LidStatus::$oudlidlike);
		$lidstatus[] = LidStatus::Overleden;
		$this->cheat = isset($_GET['rosebud']);
		$this->learnmode = isset($_GET['oefenen']);
		if (isset($_GET['verticale'])) {
			$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
			if (strlen($v) > 1) {
				$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'))->fetch();
			} else {
				$verticale = VerticalenModel::get($v);
			}
			if ($verticale) {
				$this->titel = 'Ledenmemory verticale ' . $verticale->naam . ($this->learnmode ? 'oefenen' : '');
				foreach ($verticale->getLeden() as $lid) {
					if (in_array($lid->getLidStatus(), $lidstatus)) {
						$this->leden[] = ProfielModel::get($lid->uid);
					}
				}
			}
		} else {
			$l = (int) filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
			if ($l < 1950) {
				$lichting = LichtingenModel::instance()->getJongsteLichting();
			} else {
				$lichting = LichtingenModel::get($l);
			}
			if ($lichting) {
				$this->titel = 'Ledenmemory lichting ' . $lichting->lidjaar . ($this->learnmode ? 'oefenen' : '');
				foreach ($lichting->getLeden() as $lid) {
					if (in_array($lid->lidstatus, $lidstatus)) {
						$this->leden[] = ProfielModel::get($lid->uid);
					}
				}
			}
		}
	}

	public function getModel() {
		return $this->leden;
	}

	public function getBreadcrumbs() {
		return null;
	}

	private function getPasfotoPath($uid) {
		$pasfoto = 'pasfoto/geen-foto.jpg';
		foreach (array('png', 'jpeg', 'jpg', 'gif') as $validExtension) {
			if (file_exists(PICS_PATH . 'pasfoto/' . $uid . '.' . $validExtension)) {
				$pasfoto = 'pasfoto/' . $uid . '.' . $validExtension;
				break;
			}
		}
		// kijken of de vierkante bestaat, en anders maken.
		$vierkant = PICS_PATH . 'pasfoto/' . $uid . '.vierkant.png';
		if (!file_exists($vierkant)) {
			square_crop(PICS_PATH . $pasfoto, $vierkant, 150);
		}
		return CSR_ROOT . '/plaetjes/pasfoto/' . $uid . '.vierkant.png';
	}

	private function getPasfotoMemorycard(Profiel $profiel) {
		$cheat = ($this->cheat ? $profiel->uid : '');
		$title = htmlspecialchars($this->cheat ? $profiel->voornaam . ' ' . $profiel->tussenvoegsel . ' ' . $profiel->achternaam : '');
		$flipped = ($this->learnmode ? 'flipped' : '');
		$src = $this->getPasfotoPath($profiel->uid);
		return <<<HTML
<div uid="{$profiel->uid}" class="flip memorycard pasfoto {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<img src="{$src}" title="{$title}" />
	</div>
</div>
HTML;
	}

	private function getNaamMemorycard(Profiel $profiel) {
		$cheat = ($this->cheat ? $profiel->uid : '');
		$title = htmlspecialchars($profiel->voornaam . ' ' . $profiel->tussenvoegsel . ' ' . $profiel->achternaam);
		$flipped = ($this->learnmode ? 'flipped' : '');
		$naam = ProfielModel::getNaam($profiel->uid, 'civitas');
		return <<<HTML
<div uid="{$profiel->uid}" class="flip memorycard naam {$flipped}">
	<div class="blue front">{$cheat}</div>
	<div class="blue back">
		<h3 title="{$title}">{$naam}</h3>
	</div>
</div>
HTML;
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		?><!DOCTYPE html>
		<html>
			<head>
				<?= $smarty->fetch('html_head.tpl') ?>
			</head>
			<body>
				<table>
					<tbody>
						<tr>
							<td class="pasfotos">
								<?php
								$leden = $this->leden;
								if (!$this->cheat) {
									shuffle($leden);
								}
								foreach ($leden as $lid) {
									echo $this->getPasfotoMemorycard($lid);
								}
								?>
							</td>
							<td class="namen">
								<?php
								if (!$this->cheat AND ! $this->learnmode) {
									shuffle($this->leden);
								}
								foreach ($this->leden as $lid) {
									echo $this->getNaamMemorycard($lid);
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</body>
		</html>
		<?php
	}

}
