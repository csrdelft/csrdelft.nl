<?php

namespace CsrDelft\view\profiel;

use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\SaldoGrafiekModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\fotoalbum\FotoTagsModel;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\maalcie\KwalificatiesModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\SmartyTemplateView;

/**
 * ProfielView.php
 *
 * @author C.S.R. Delft  <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property Profiel $model
 */
class ProfielView extends SmartyTemplateView {

	function __construct(Profiel $profiel) {
		parent::__construct($profiel, 'Het profiel van ' . $profiel->getNaam());
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <span class="active">' . $this->model->getNaam('civitas') . '</span>';
	}

	function view() {
		$this->smarty->assign('profiel', $this->model);

		$kring = $this->model->getKring();
		if ($kring) {
			$html = '<a href="' . $kring->getUrl() . '">' . $kring->naam;
			if ($this->model->status === LidStatus::Kringel) {
				$html .= ' (kringel)';
			} elseif ($kring->getLid($this->model->uid)->opmerking === 'Leider') {
				$html .= ' (kringleider)';
			} elseif ($this->model->verticaleleider) {
				$html .= ' (leider)';
			} elseif ($this->model->kringcoach) {
				$html .= ' <span title="Kringcoach van verticale ' . VerticalenModel::get($this->model->verticale)->naam . '">(kringcoach)</span>';
			}
			$html .= '</a>';
			$this->smarty->assign('kring', $html);
		} else {
			$this->smarty->assign('kring', false);
		}

		$woonoord = $this->model->getWoonoord();
		if ($woonoord) {
			$this->smarty->assign('woonoord', '<a href="' . $woonoord->getUrl() . '" class="dikgedrukt">' . $woonoord->naam . '</a>');
		} else {
			$this->smarty->assign('woonoord', '');
		}

		$besturen = '';
		foreach (BesturenModel::instance()->getGroepenVoorLid($this->model->uid) as $bestuur) {
			$besturen .= '<a href="' . $bestuur->getUrl() . '">' . $bestuur->naam . '</a><br />';
		}
		if ($besturen != '') {
			$besturen = '<div class="label">Bestuur:</div><div class="data">' . $besturen . '</div><br />';
		}
		$this->smarty->assign('besturen', $besturen);

		$commissies = '';
		foreach (CommissiesModel::instance()->getGroepenVoorLid($this->model->uid) as $commissie) {
			$commissies .= '<a href="' . $commissie->getUrl() . '">' . $commissie->naam . '</a><br />';
		}
		if ($commissies != '') {
			$commissies = '<div class="label">Commissies:</div><div class="data">' . $commissies . '</div><br />';
		}
		$this->smarty->assign('commissies', $commissies);


		$werkgroepen = '';
		foreach (WerkgroepenModel::instance()->getGroepenVoorLid($this->model->uid) as $werkgroep) {
			$werkgroepen .= '<a href="' . $werkgroep->getUrl() . '">' . $werkgroep->naam . '</a><br />';
		}
		if ($werkgroepen != '') {
			$werkgroepen = '<div class="label">Werkgroepen:</div><div class="data">' . $werkgroepen . '</div><br />';
		}
		$this->smarty->assign('werkgroepen', $werkgroepen);

		$onderverenigingen = '';
		foreach (OnderverenigingenModel::instance()->getGroepenVoorLid($this->model->uid) as $ondervereniging) {
			$onderverenigingen .= '<a href="' . $ondervereniging->getUrl() . '">' . $ondervereniging->naam . '</a><br />';
		}
		if ($onderverenigingen != '') {
			$onderverenigingen = '<div class="label">Onder-<br />verenigingen:</div><div class="data">' . $onderverenigingen . '</div><br />';
		}
		$this->smarty->assign('onderverenigingen', $onderverenigingen);

		$groepen = '';
		foreach (RechtenGroepenModel::instance()->getGroepenVoorLid($this->model->uid) as $groep) {
			$groepen .= '<a href="' . $groep->getUrl() . '">' . $groep->naam . '</a><br />';
		}
		if ($groepen != '') {
			$groepen = '<div class="label">Overige<br />groepen:</div><div class="data">' . $groepen . '</div><br />';
		}
		$this->smarty->assign('groepen', $groepen);

		$ketzers = '';
		foreach (KetzersModel::instance()->getGroepenVoorLid($this->model->uid) as $ketzer) {
			$ketzers .= '<a href="' . $ketzer->getUrl() . '">' . $ketzer->naam . '</a><br />';
		}
		if ($ketzers != '') {
			$ketzers = '<div class="label">Aanschaf-<br />ketzers:</div><div class="data">' . $ketzers . '</div><br />';
		}
		$this->smarty->assign('ketzers', $ketzers);

		$activiteiten = '';
		foreach (ActiviteitenModel::instance()->getGroepenVoorLid($this->model->uid) as $activiteit) {
			$activiteiten .= '<a href="' . $activiteit->getUrl() . '">' . $activiteit->naam . '</a><br />';
		}
		if ($activiteiten != '') {
			$activiteiten = '<div class="label">Activiteiten:</div><div class="data">' . $activiteiten . '</div><br />';
		}
		$this->smarty->assign('activiteiten', $activiteiten);

		if (LoginModel::getUid() == $this->model->uid || LoginModel::mag('P_MAAL_MOD')) {
			$timestamp = strtotime(InstellingenModel::get('maaltijden', 'recent_lidprofiel'));
			$this->smarty->assign('recenteAanmeldingen', MaaltijdAanmeldingenModel::instance()->getRecenteAanmeldingenVoorLid($this->model->uid, $timestamp));

			$this->smarty->assign('abos', MaaltijdAbonnementenModel::instance()->getAbonnementenVoorLid($this->model->uid));
		}

		if (SaldoGrafiekModel::magGrafiekZien($this->model->uid)) {
			$this->smarty->assign('saldografiek', 'ja');
		}

		$bestellingen = CiviBestellingModel::instance()->getBestellingenVoorLid($this->model->uid, 10);
		$bestellinglog = CiviBestellingModel::instance()->getBeschrijving($bestellingen);
		$this->smarty->assign('bestellinglog', $bestellinglog);
		$this->smarty->assign('bestellingenlink', '/fiscaat/bestellingen' . (LoginModel::getUid() === $this->model->uid ? '' : '/' . $this->model->uid));

		$this->smarty->assign('corveepunten', $this->model->corvee_punten);
		$this->smarty->assign('corveebonus', $this->model->corvee_punten_bonus);

		$this->smarty->assign('corveetaken', CorveeTakenModel::instance()->getTakenVoorLid($this->model->uid));

		$this->smarty->assign('corveevoorkeuren', CorveeVoorkeurenModel::instance()->getVoorkeurenVoorLid($this->model->uid));

		$this->smarty->assign('corveevrijstelling', CorveeVrijstellingenModel::instance()->getVrijstelling($this->model->uid));

		$this->smarty->assign('corveekwalificaties', KwalificatiesModel::instance()->getKwalificatiesVanLid($this->model->uid));

		$this->smarty->assign('forumpostcount', ForumPostsModel::instance()->getAantalForumPostsVoorLid($this->model->uid));


		$exemplaren = BoekExemplaarModel::getEigendom($this->model->uid);
		$boekenEigendom = [];
		foreach ($exemplaren as $exemplaar) {
			$boekenEigendom[] = $exemplaar->getBoek();
		}
		$this->smarty->assign('boeken', $boekenEigendom);
		$recensies = BoekRecensieModel::getVoorLid($this->model->uid);
		$boekenGerecenseerd = [];
		foreach ($recensies as $recensie) {
			$boekenGerecenseerd[] = $recensie->getBoek();
		}
		$this->smarty->assign('gerecenseerdeboeken', $boekenGerecenseerd);

		$fotos = array();
		foreach (FotoTagsModel::instance()->find('keyword = ?', array($this->model->uid), null, null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = FotoModel::instance()->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}
		$this->smarty->assign('fotos', $fotos);

		$this->smarty->display('profiel/profiel.tpl');
	}

}
