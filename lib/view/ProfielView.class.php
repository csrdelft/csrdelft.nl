<?php

/**
 * ProfielView.class.php
 * 
 * @author C.S.R. Delft	<pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
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

			require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';
			$timestamp = strtotime(Instellingen::get('maaltijden', 'recent_lidprofiel'));
			$this->smarty->assign('recenteAanmeldingen', MaaltijdAanmeldingenModel::getRecenteAanmeldingenVoorLid($this->model->uid, $timestamp));

			require_once 'model/maalcie/MaaltijdAbonnementenModel.class.php';
			$this->smarty->assign('abos', MaaltijdAbonnementenModel::getAbonnementenVoorLid($this->model->uid));
		}

		require_once 'lid/saldi.class.php';
		if (Saldi::magGrafiekZien($this->model->uid)) {
			$this->smarty->assign('saldografiek', Saldi::getDatapoints($this->model->uid, 60));
		}

		$this->smarty->assign('corveepunten', $this->model->corvee_punten);
		$this->smarty->assign('corveebonus', $this->model->corvee_punten_bonus);

		require_once 'model/maalcie/CorveeTakenModel.class.php';
		$this->smarty->assign('corveetaken', CorveeTakenModel::getTakenVoorLid($this->model->uid));

		require_once 'model/maalcie/CorveeVoorkeurenModel.class.php';
		$this->smarty->assign('corveevoorkeuren', CorveeVoorkeurenModel::getVoorkeurenVoorLid($this->model->uid));

		require_once 'model/maalcie/CorveeVrijstellingenModel.class.php';
		$this->smarty->assign('corveevrijstelling', CorveeVrijstellingenModel::getVrijstelling($this->model->uid));

		require_once 'model/maalcie/KwalificatiesModel.class.php';
		$this->smarty->assign('corveekwalificaties', KwalificatiesModel::instance()->getKwalificatiesVanLid($this->model->uid));

		require_once 'model/ForumModel.class.php';
		$this->smarty->assign('forumpostcount', ForumPostsModel::instance()->getAantalForumPostsVoorLid($this->model->uid));

		require_once 'model/bibliotheek/BiebCatalogus.class.php';
		$this->smarty->assign('boeken', BiebCatalogus::getBoekenByUid($this->model->uid, 'eigendom'));
		$this->smarty->assign('gerecenseerdeboeken', BiebCatalogus::getBoekenByUid($this->model->uid, 'gerecenseerd'));

		require_once 'view/FotoAlbumView.class.php';
		$fotos = array();
		foreach (FotoTagsModel::instance()->find('keyword = ?', array($this->model->uid), null, null, 3) as $tag) {
			$foto = FotoModel::instance()->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}
		$this->smarty->assign('fotos', $fotos);

		$this->smarty->display('profiel/profiel.tpl');
	}

}

class ProfielForm extends Formulier {

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » ' . $this->model->getLink('civitas');
	}

	public function __construct(Profiel $profiel) {
		if ($profiel->uid) {
			parent::__construct($profiel, '/profiel/' . $profiel->uid . '/bewerken');
		} else {
			parent::__construct($profiel, '/profiel/' . $profiel->lidjaar . '/nieuw/' . strtolower(substr($profiel->status, 2)));
		}

		$admin = LoginModel::mag('P_LEDEN_MOD');
		$inschrijven = !$profiel->getAccount();

		if ($inschrijven) {
			$this->titel = 'Welkom bij C.S.R.!';
			$fields[] = new HtmlComment('<p>
				Hieronder mag je gegevens invullen in het databeest van de Civitas. Zo kunnen we contact met je houden,
				kunnen andere leden opzoeken waar je woont en kun je (na het novitiaat) op het forum berichten plaatsen.
			</p>');
		} else {
			$this->titel = 'Bewerk het profiel van ' . $profiel->getNaam('volledig');
			$fields[] = new HtmlComment('<p>
				Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
				wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
				een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
				dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:vice-abactis@csrdelft.nl">Vice-Abactis</a>.
			</p>');
		}
		$fields[] = new HtmlComment('<p>
			Als er <span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten aanpassen aan het
			gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.
		</p>');

		if ($admin) {
			$statussen = array();
			foreach (LidStatus::getTypeOptions() as $optie) {
				$statussen[$optie] = LidStatus::getDescription($optie);
			}
			$fields[] = new SelectField('status', $profiel->status, 'Lidstatus', $statussen);
			$fields[] = new HtmlComment('<p>Bij het wijzigen van de lidstatus worden overbodige <span class="waarschuwing">gegevens verwijderd</span>, onomkeerbaar, opletten dus!</p>');

			require_once 'lid/lidzoeker.class.php';
			if ($profiel->voornaam == '') {
				$gelijknamigenovieten = array();
			} else {
				$gelijknamigenovieten = LidZoeker::zoekLeden($profiel->voornaam, 'voornaam', 'alle', 'achternaam', array(LidStatus::Noviet), array('uid'));
			}
			if ($profiel->achternaam == '') {
				$gelijknamigeleden = array();
			} else {
				$gelijknamigeleden = LidZoeker::zoekLeden($profiel->achternaam, 'achternaam', 'alle', 'lidjaar', array(LidStatus::Lid, LidStatus::Gastlid), array('uid'));
			}

			$html = '<div class="novieten">';
			if (count($gelijknamigenovieten) > 1 OR ( $profiel->status !== LidStatus::Noviet AND ! empty($gelijknamigenovieten))) {
				$html .= 'Gelijknamige novieten:<ul class="nobullets">';
				foreach ($gelijknamigenovieten as $noviet) {
					$html .= '<li>' . ProfielModel::getLink($noviet['uid'], 'volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen novieten met overeenkomstige namen.';
			}
			$html .= '</div><div class="leden">';
			if (count($gelijknamigeleden) > 1 OR ( !($profiel->status == LidStatus::Lid OR $profiel->status == LidStatus::Gastlid) AND ! empty($gelijknamigeleden))) {
				$html .= 'Gelijknamige (gast)leden:<ul class="nobullets">';
				foreach ($gelijknamigeleden as $lid) {
					$html .= '<li>' . ProfielModel::getLink($lid['uid'], 'volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen (gast)leden met overeenkomstige namen.';
			}
			$html .= '</div>';

			$fields[] = new HtmlComment($html);
		}

		if ($admin OR $inschrijven OR $profiel->isOudlid()) {
			$fields[] = new Subkopje('Identiteit');
			$fields[] = new RequiredTextField('voornaam', $profiel->voornaam, 'Voornaam', 50);
			$fields[] = new RequiredTextField('voorletters', $profiel->voorletters, 'Voorletters', 10);
			$fields[] = new TextField('tussenvoegsel', $profiel->tussenvoegsel, 'Tussenvoegsel', 15);
			$fields[] = new RequiredTextField('achternaam', $profiel->achternaam, 'Achternaam', 50);
			if ($admin OR $inschrijven) {
				$fields[] = new RequiredGeslachtField('geslacht', $profiel->geslacht, 'Geslacht');
				$fields[] = new TextField('voornamen', $profiel->voornamen, 'Voornamen', 100);
				if (!$inschrijven) {
					$fields[] = new TextField('postfix', $profiel->postfix, 'Postfix', 7);
					$fields[] = new TextField('nickname', $profiel->nickname, 'Bijnaam', 20);
				}
			}
			$fields[] = new RequiredDateField('gebdatum', $profiel->gebdatum, 'Geboortedatum', date('Y') - 15);
			if ($admin AND $profiel->status === LidStatus::Overleden) {
				$fields[] = new DateField('sterfdatum', $profiel->sterfdatum, 'Overleden op');
			}
			if (($admin OR $profiel->isOudlid() OR $profiel->status === LidStatus::Overleden) AND ! $inschrijven) {
				$fields[] = new LidField('echtgenoot', $profiel->echtgenoot, 'Echtgenoot', 'allepersonen');
				$fields[] = new Subkopje('Oudledenpost');
				$fields[] = new TextField('adresseringechtpaar', $profiel->adresseringechtpaar, 'Tenaamstelling post echtpaar', 250);

				$contactueel = array();
				foreach (OntvangtContactueel::getTypeOptions() as $optie) {
					$contactueel[$optie] = OntvangtContactueel::getDescription($optie);
				}
				$fields[] = new SelectField('ontvangtcontactueel', $profiel->ontvangtcontactueel, 'Ontvangt Contactueel?', $contactueel);
			}
		}

		$fields[] = new Subkopje('Adres');
		$fields[] = new RequiredTextField('adres', $profiel->adres, 'Straatnaam + Huisnummer', 100);
		$fields[] = new RequiredTextField('postcode', $profiel->postcode, 'Postcode', 20);
		$fields[] = new RequiredTextField('woonplaats', $profiel->woonplaats, 'Woonplaats', 50);
		$fields[] = new RequiredLandField('land', $profiel->land, 'Land');

		if (!$profiel->isOudlid()) {
			$fields[] = new Subkopje('Adres ouders');
			$fields[] = new TextField('o_adres', $profiel->o_adres, 'Straatnaam + Huisnummer', 100);
			$fields[] = new TextField('o_postcode', $profiel->o_postcode, 'Postcode', 20);
			$fields[] = new TextField('o_woonplaats', $profiel->o_woonplaats, 'Woonplaats', 50);
			$fields[] = new LandField('o_land', $profiel->o_land, 'Land', 50);
			$fields[] = new TelefoonField('o_telefoon', $profiel->o_telefoon, 'Telefoonnummer', 20);
		}

		$fields[] = new Subkopje('Contact');
		//TODO: email & multiple contacts
		$fields['email'] = new RequiredEmailField('email', $profiel->email, 'E-mailadres');
		if (!$inschrijven) {
            $fields['email']->readonly = true;
            $fields['email']->title = 'Wijzig je e-mailadres met het inloggegevens-formulier.';
            $fields[] = new EmailField('msn', $profiel->msn, 'MSN');
            $fields[] = new TextField('icq', $profiel->icq, 'ICQ', 10);
            $fields[] = new EmailField('jid', $profiel->jid, 'Jabber/Google-talk');
            $fields[] = new TextField('skype', $profiel->skype, 'Skype', 20);
            $fields[] = new UrlField('linkedin', $profiel->linkedin, 'Publiek LinkedIn-profiel');
            $fields[] = new UrlField('website', $profiel->website, 'Website');
        }
        $fields[] = new RequiredTelefoonField('mobiel', $profiel->mobiel, 'Mobiel', 20);
        $fields[] = new TelefoonField('telefoon', $profiel->telefoon, 'Telefoonnummer (vast)', 20);

		$fields[] = new Subkopje('Boekhouding');
		$fields[] = new RequiredIBANField('bankrekening', $profiel->bankrekening, 'Bankrekening', 18);
		if ($admin) {
			$fields[] = new JaNeeField('machtiging', $profiel->machtiging, 'Machtiging getekend?');
		}
		if (LoginModel::mag('P_ADMIN')) {
			$fields[] = new IntField('soccieID', (int) $profiel->soccieID, 'SoccieID (uniek icm. bar)', 0, 10000);
			$fields[] = new SelectField('createTerm', $profiel->createTerm, 'Aangemaakt bij', array('barvoor' => 'barvoor', 'barmidden' => 'barmidden', 'barachter' => 'barachter', 'soccie' => 'soccie'));
		}

		$fields[] = new Subkopje('Studie');
		$fields[] = new StudieField('studie', $profiel->studie, 'Studie');
		$fields['studiejaar'] = new IntField('studiejaar', (int) $profiel->studiejaar, 'Beginjaar studie', 1950, date('Y'));
		$fields['studiejaar']->leden_mod = $admin;

		if (!$profiel->isOudlid()) {
			$fields[] = new TextField('studienr', $profiel->studienr, 'Studienummer (TU)', 20);
		}

		if (!$inschrijven AND ( $admin OR $profiel->isOudlid() )) {
			$fields[] = new TextField('beroep', $profiel->beroep, 'Beroep/werk', 4096);
			$fields[] = new IntField('lidjaar', (int) $profiel->lidjaar, 'Lid sinds', 1950, date('Y'));
			$fields[] = new DateField('lidafdatum', $profiel->lidafdatum, 'Lid-af sinds');
		}

		if ($admin AND ! $inschrijven) {
			$fields[] = new VerticaleField('verticale', $profiel->verticale, 'Verticale');
			if ($profiel->isLid()) {
				$fields[] = new JaNeeField('verticaleleider', $profiel->verticaleleider, 'Verticaleleider');
				$fields[] = new JaNeeField('kringcoach', $profiel->kringcoach, 'Kringcoach');
			}
			$fields[] = new LidField('patroon', $profiel->patroon, 'Patroon', 'allepersonen');
		}

		if (!$inschrijven) {
			$fields[] = new Subkopje('Duckstad');
			$fields[] = new DuckField('duckname', $profiel->duckname);
			$duckfoto = new Afbeelding(PHOTOS_PATH . $profiel->getPasfotoPath(false, 'Duckstad'));
			if (!$duckfoto->exists() OR strpos($duckfoto->directory, '/Duckstad/') === false) {
				$duckfoto = null;
			}
			$fields[] = new ImageField('duckfoto', 'Duck-pasfoto', $duckfoto, null, null, false, 100, 100, 250, 250);
		}

		$fields[] = new Subkopje('Persoonlijk');
		$fields[] = new TextField('eetwens', $profiel->eetwens, 'Dieet/voedselallergie');
		$fields[] = new RequiredIntField('lengte', (int) $profiel->lengte, 'Lengte (cm)', 50, 250);
		$fields[] = new SelectField('ovkaart', $profiel->ovkaart, 'OV-kaart', array('' => 'Kies...', 'geen' => '(Nog) geen OV-kaart', 'week' => 'Week', 'weekend' => 'Weekend', 'niet' => 'Niet geactiveerd'));
		$fields[] = new TextField('kerk', $profiel->kerk, 'Kerk', 50);
		$fields[] = new TextField('muziek', $profiel->muziek, 'Muziekinstrument', 50);
		$fields[] = new SelectField('zingen', $profiel->zingen, 'Zingen', array('' => 'Kies...', 'ja' => 'Ja, ik zing in een band/koor', 'nee' => 'Nee, ik houd niet van zingen', 'soms' => 'Alleen onder de douche', 'anders' => 'Anders'));

		if ($admin OR $inschrijven) {
			$fields[] = new TextField('vrienden', $profiel->vrienden, 'Vrienden binnnen C.S.R.', 300);
			$fields[] = new TextField('middelbareSchool', $profiel->middelbareSchool, 'Middelbare school', 200);
		}

        $fields[] = new Subkopje('<b>Einde vragenlijst</b><br /><br /><br /><br /><br />');
		if ($admin OR LoginModel::mag('commissie:NovCie')) {
			$fields[] = new CollapsableSubkopje('novcieForm', 'In te vullen door NovCie', true);
            $fields[] = new RequiredTextareaField('novitiaat', $profiel->novitiaat, 'Wat verwacht Noviet van novitiaat?');
			$fields[] = new RequiredSelectField('novietSoort', $profiel->novietSoort, 'Soort Noviet', array('noviet', 'nanoviet'));
			$fields[] = new RequiredSelectField('matrixPlek', $profiel->matrixPlek, 'Matrix plek', array('voor', 'midden', 'achter'));
			$fields[] = new RequiredSelectField('startkamp', $profiel->startkamp, 'Startkamp', array('ja', 'nee'));
			$fields[] = new TextareaField('medisch', $profiel->medisch, 'medisch (NB alleen als relevant voor hele NovCie, bijv. allergieen)');
			$fields[] = new TextareaField('novitiaatBijz', $profiel->novitiaatBijz, 'Bijzonderheden novitiaat (op dag x ...)');
			$fields[] = new TextareaField('kgb', $profiel->kgb, 'Overige NovCie-opmerking');
			$fields[] = new HtmlComment('</div>');
		}
		$fields[] = new FormDefaultKnoppen('/profiel/' . $profiel->uid);

		$this->addFields($fields);
	}

}
