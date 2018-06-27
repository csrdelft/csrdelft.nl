<?php
namespace CsrDelft\view\ledenmemory;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CompressedLayout;
use CsrDelft\view\CsrSmarty;

/**
 * LedenMemoryView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Het spelletje memory met pasfotos en namen van leden
 */
class LedenMemoryView extends CompressedLayout {

private $leden = array();
private $learnmode;
private $cheat;
private $groep;

public function __construct() {
	$lidstatus = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
	$lidstatus[] = LidStatus::Overleden;
	$this->groep = array();
	$this->cheat = isset($_GET['rosebud']);
	$this->learnmode = isset($_GET['oefenen']);
	switch (isset($_GET['verticale'])) {

		case true:
			$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
			$verticale = false;
			if (strlen($v) == 1) {
				$verticale = VerticalenModel::get($v);
			}
			if (!$verticale) {
				$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
			}
			if ($verticale) {
				$this->titel = $verticale->naam . ' verticale ledenmemory' . ($this->learnmode ? ' (oefenen)' : '');
				$this->groep = $verticale;
				break;
			}
		// fall through

		case false:
			$l = (int)filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
			$min = LichtingenModel::getOudsteLidjaar();
			$max = LichtingenModel::getJongsteLidjaar();
			if ($l < $min OR $l > $max) {
				$l = $max;
			}
			$lichting = LichtingenModel::get($l);
			if ($lichting) {
				$this->titel = $lichting->lidjaar . ' lichting ledenmemory' . ($this->learnmode ? ' (oefenen)' : '');
				$this->groep = $lichting;
			}
	}
	if ($this->groep instanceof AbstractGroep) {
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if (in_array($profiel->status, $lidstatus)) {
				$this->leden[] = $profiel;
			}
		}
	}
	$this->addScript('/dist/js/manifest.js');
	$this->addScript('/dist/js/vendor.js');
	$this->addScript('/dist/js/ledenmemory.js');
}

public function getLayout() {
	return 'layout';
}

public function getModel() {
	return $this->leden;
}

public function getBreadcrumbs() {
	return null;
}

private function getPasfotoPath(Profiel $profiel) {
    return "/plaetjes/pasfoto/".$profiel->getPasfotoPath(true);
}

private function getPasfotoMemorycard(Profiel $profiel) {
	$cheat = ($this->cheat ? $profiel->uid : '');
	$title = htmlspecialchars($this->cheat ? $profiel->voornaam . ' ' . $profiel->tussenvoegsel . ' ' . $profiel->achternaam : '');
	$flipped = ($this->learnmode ? 'flipped' : '');
	$src = $this->getPasfotoPath($profiel);
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
<body data-groep="<?= $this->groep->getUUID() ?>">
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
			if (!$this->cheat AND !$this->learnmode) {
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
