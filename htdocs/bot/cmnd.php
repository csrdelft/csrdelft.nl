<?php
#
# htdocs/bot/cmnd.php $Id$
#
# Dit script exporteert functies uit de website voor de gozerbot-plugin
# 'csrdelft'.
#
# Authenticatie van de bot moet buiten het script om gebeuren, door permissies
# op het uitvoeren van het script via de webserver, en het definieren van een
# constante in include.config.php, ENABLE_BOT_CMND, die de waarde true moet
# hebben.Deze constante is standaard (lees: in vers uitgechekte svn) op false
# ingesteld uit veiligheidsoverwegingen.
#
# In het request aan dit script staat gedefinieerd onder wiens rechten de
# functies uitgevoerd worden. de bot moet dus te vertrouwen zijn.
#
# Een request is gecodeerd als HTTP GET-request, met de volgende parameters:
#    fn -> functie die aangeroepen wordt
#   uid -> lid-nr onder wiens rechten het uitgevoerd wordt (!)
#
# Hiernaast kan elke functie die beschikbaar is extra parameters vereisen, deze
# worden gespecificeerd bij het definieren van de interface
#
# Als resultaat geeft dit script een json-encoded array terug als utf8-string.
# Deze wordt door de bot ingelezen, wat resulteert in een python list of dict,
# afhankelijk van het gebruik van keys in de array in PHP.
#
# Inspringen van code wijkt in dit bestand af van de norm in de rest van de
# code van de website. in plaats van tabs worden 4 spaties gebruikt. de reden
# voor deze keuze is puur uit praktische overwegingen omdat dit script meestal
# tegelijk met de bijbehorende pythoncode wordt bewerkt.
#
# Hans van Kranenburg ;]
#

header("content-type: text/plain");
define('MODE', 'BOT');
require_once('include.config.php');

# verplichte parameters
if ( !defined('ENABLE_BOT_CMND') or
     constant('ENABLE_BOT_CMND') !== true or
     !isset($_GET['fn']) or
     !isset($_GET['uid']) or
     !$lid->login($_GET['uid'])
   ) {
    echo "[]";
    exit(1);
}
$fn  = $_GET['fn'];
$uid = $_GET['uid'];

#################################################################################
# functiedefinities
#
# cmnds is een array met daarin als key de functienamen die gedefinieerd zijn en
# als value een array van extra parameters: string parameter => boolean required.
# de waarde van deze extra parameters worden meegegeven in een array als tweede
# argument naast de uid aan een functie die aangeroepen wordt.
# als een parameter met de vlag required true is opgegeven, en de parameter niet
# voorkomt in het GET-request, wordt nog voordat de functie aangeroepen zou worden
# 
#
$cmnds = array();

/*
 * getuserhosts: Opvragen van IM-adressen in een profiel.
 * Wordt ook gebruikt tijdens het leggen van de koppeling mbv csr-setuid.
 * Die koppeling is de enige keer dat deze functie door de bot aangeroepen wordt
 * zonder dat een koppeling tussen het uid en de bot-user bekend is... (!)
 *
 * params: getuid
 * uitvoer:
 * {
 *     'naam': civitasnaam,
 *     'userhosts':
 *     {
 *         'msn': msn-adres,
 *         'icq': icq-adres,
 *         'jid': jabber-adres
 *     }
 * }
 *
 */
function getuserhosts($uid, $params) {
    global $lid;
    if ($params['getuid'] == '') {
        $profiel = $lid->getLid()->getProfiel();
        $naam = $lid->getLid()->getNaamLink('civitas','plain');
    } else {
        $profiel = anderprofiel($params['getuid']);
        if ($profiel === false) return array("Profiel niet gevonden in de ledenlijst");
        # N.B. getCivitasName() 'werkt niet' voor een class.profiel object
        $naam = LidCache::getLid($params['getuid'])->getNaamLink('civitas', 'plain');
    }
    $userhosts = array();
    if ($profiel['msn'] != "") $userhosts[] = $profiel['msn'];
    if ($profiel['icq'] != "") $userhosts[] = $profiel['icq'] . "@login.icq.com";
    if ($profiel['jid'] != "") $userhosts[] = $profiel['jid'];
    return array(
        'naam' => $naam,
        'userhosts' => $userhosts
    );
}
$cmnds['getuserhosts'] = array ('getuid' => false);

/*
 * getsaldo: Opvragen van soccie/maalciesaldo
 * Het is alleen mogelijk eigen saldo op te vragen
 * 
 * params: -
 * uitvoer: {'soccieSaldo': socciesaldo, 'maalcieSaldo': maalciesaldo}
 *
 */
function getsaldo($uid, $params) {
    global $lid;
    return $lid->getLid()->getSaldi();
}
$cmnds['getsaldo'] = array();

/*
 * abolijst: Opvragen van actieve maaltijdabo's
 *
 * params: -
 * uitvoer: ['abosoort', 'abosoort', ... ]
 *
 */
function abolijst($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $myabos = $maaltrack->getAbo();
    if (count($myabos) > 0 ) return array_values($myabos);
    return array();
}
$cmnds['abolijst'] = array();

/*
 * getwelabos: Opvragen maaltijdabo's die uitgezet kunnen worden.
 * Wordt gebruikt als delabo in de bot zonder parameter is aangeroepen.
 *
 * params: -
 * uitvoer: ['foutmelding'] of ['abosoort (verkort)', 'abosoort (verkort)', ... ]
 *
 */
function getwelabos($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $result = array();
    $abos = $maaltrack->getAbo();
    foreach ($abos as $key => $value)
        $result[] = sprintf("%s (%s)", $value, str_replace('a_', '', strtolower($key)));
    if (count($result) > 0 ) return $result;
    return array("Er is geen maaltijdabonnement dat geactiveerd is");
}
$cmnds['getwelabos'] = array();

/*
 * getnotabos: Opvragen maaltijdabo's die aangezet kunnen worden
 * Wordt gebruikt als addabo in de bot zonder parameter is aangeroepen.
 * 
 * params: -
 * uitvoer: ['foutmelding'] of ['abosoort (verkort)', 'abosoort (verkort)', ... ]
 *
 */
function getnotabos($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $result = array();
    $abos = $maaltrack->getNotAboSoort();
    foreach ($abos as $key => $value)
        $result[] = sprintf("%s (%s)", $value, str_replace('a_', '', strtolower($key)));
    if (count($result) > 0 ) return $result;
    return array("U heeft alle maaltijdabonnementen inmiddels geactiveerd");
}
$cmnds['getnotabos'] = array();

/*
 * addabo: Abo toevoegen aan de maaltijd-abo's
 *
 * params: abosoort
 * uitvoer: ['melding']
 *
 */
function addabo($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $abosoort = 'A_' . strtoupper($params['abosoort']);
    if ($maaltrack->addAbo($abosoort))
        return array(sprintf("Het maaltijdabonnement '%s' is nu geactiveerd.", $maaltrack->getAboTekst($abosoort)));
    return array($maaltrack->getError());
}
$cmnds['addabo'] = array('abosoort' => true);

/*
 * delabo: Abo verwijderen uit de maaltijd-abo's
 *
 * params: abosoort
 * uitvoer: ['melding']
 *
 */
function delabo($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $abosoort = 'A_' . strtoupper($params['abosoort']);
    if ($maaltrack->delAbo($abosoort))
        return array(sprintf("Het maaltijdabonnement '%s' is nu uitgezet.", $maaltrack->getAboTekst($abosoort)));
    return array($maaltrack->getError());
}
$cmnds['delabo'] = array('abosoort' => true);

/*
 * getjarig: Opvragen komende 10 verjaardagen.
 *
 * params: -
 * uitvoer: ["datum naam (leeftijd)", "datum naam (leeftijd)", ... ]
 *
 */
function getjarig($uid, $params) {
	require_once('lid/class.mootverjaardag.php');
	$verj10 = Verjaardag::getKomendeVerjaardagen(10);
    $result = array();
    foreach ($verj10 as $verj) {
		$lid=LidCache::getLid($verj['uid']);
		$naam = $lid->getNaamLink('civitas', 'plain');
        $datum = date("j-n", mktime(0,0,0,date('m'),date('j')+$verj['jarig_over']));
        $result[] = sprintf('%s %s (%s)', $datum, $naam, $verj['leeftijd']);
    }
    return $result;
}
$cmnds['getjarig'] = array();

/*
 * getprofiel: Opvragen van een profiel
 *
 * params: getuid
 * uitvoer: ['Jan Lid', 'Oude Delft 9 2611 BA Delft', ... ]
 *
 */
function getprofiel($uid, $params) {
    global $lid;
    if ($params['getuid'] == '') {
        $profiel = $lid->getLid()->getProfiel();
    } else {
        $profiel = anderprofiel($params['getuid']);
        if ($profiel === false) return array("Profiel niet gevonden in de ledenlijst");
    }
    return profiel_to_botarray($profiel);
}
$cmnds['getprofiel'] = array('getuid' => false);

/*
 * aaidrom: geintje, 'omdraai', omdraaien van namen
 *
 * params: getuid
 * uitvoer: ['Lan Jid']
 *
 */
function aaidrom($uid, $params) {
    global $lid;
    if ($params['getuid'] == '') {
		$lid=$lid->getLid();
		$profiel = $lid->getProfiel();
        return $lid->getNaamLink('aaidrom', 'plain');
    } else {
		$lid=LidCache::getLid($params['getuid']);
		$profiel = $lid->getProfiel();
        if (!is_array($profiel)) return array("Profiel niet gevonden in de ledenlijst");
        return $lid->getNaamLink('aaidrom', 'plain');
    }
}
$cmnds['aaidrom'] = array('getuid' => false);

/*
 * whoami: Eigen naam opvragen
 * 
 * params: -
 * uitvoer: ['Jan Lid']
 *
 */
function whoami($uid, $params) {
    global $lid;
    return array($lid->getLid()->getNaam());
}
$cmnds['whoami'] = array();

/*
 * perms: Permissies opvragen die de gebruiker op de website heeft.
 *
 * params: -
 * uitvoer: ['P_ANYTHING']
 *
 */
function perms($uid, $params) {
    return array(LoginLid::getLid()->getPermissions());
}
$cmnds['perms'] = array();

/*
 * zoek: Zoeken in de ledenlijst, leden
 * (zie 'zoekoud' voor oudleden zoeken)
 *
 * params: zoekterm
 * uitvoer: ['<uid> Naam', '<uid> Naam', ... ] of het profiel als er maar
 *   1 resultaat is
 *
 */
function zoeklid($uid, $params) {
    global $lid;
    $leden = Zoeker::zoekLeden(urldecode($params['zoekterm']), 'naam', 'alle', 'uid', 'leden');
    if (count($leden) == 1) {
        $profiel = anderprofiel($leden[0]['uid']);
        return profiel_to_botarray($profiel);
    }
    $result = array(); # array bouwen van naam en uid
    foreach ($leden as $l) $result[] = $l['uid'] . " " . LidCache::getLid($l['uid'])->getNaamLink('civitas', 'plain');
    return $result;
}
$cmnds['zoeklid'] = array('zoekterm' => true);

/*
 * zoekoud: Zoeken in de oudledenlijst, leden
 * (zie 'zoek' voor leden zoeken)
 *
 * params: zoekterm
 * uitvoer: ['<uid> Naam', '<uid> Naam', ... ] of het profiel als er maar
 *   1 resultaat is
 *
 */
function zoekoud($uid, $params) {
    global $lid;
    $leden =Zoeker::zoekLeden(urldecode($params['zoekterm']), 'naam', 'alle', 'uid', 'oudleden');
    if (count($leden) == 1) {
        $profiel = anderprofiel($leden[0]['uid']);
        return profiel_to_botarray($profiel);
    }
    $result = array(); # array bouwen van naam en uid
    foreach ($leden as $l){
		$result[] = $l['uid'] . " " . LidCache::getLid($l['uid'])->getNaamLink('civitas', 'plain');
	}
    return $result;
}
$cmnds['zoekoud'] = array('zoekterm' => true);

/*
 * maallijst: Een lijstje met komende maaltijden maken
 *
 * params: -
 * uitvoer: ['$id) $datum, $tekst ($status) GESLOTEN', '$id) $datum, $tekst ($status) VOL', ... ]
 * 
 */
function maallijst($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    # opvragen komende maaltijden + onze status (zijn we ingeschreven etc)
    $nu = time();
    $lijst = $maaltrack->getMaaltijden($nu-7200, $nu+MAALTIJD_LIJST_MAX_TOT);
    # we maken een lijstje met tekst die zo door de bot als list geprint kan worden
    $botlijst = array();
    foreach ($lijst as $l) {
        $error = ($l['max'] <= $l['aantal']) ? ' (VOL)' : '';
        $error .= ($l['gesloten']) ? ' (GESLOTEN)' : '';
        if ($l['status'] == '') $l['status'] = 'AF';
        $botlijst[] = sprintf('%s) %s, %s (%s)%s'
            , $l['id']
            , str_replace('  ',' ',strftime("%a %e %b '%y %H:%M", $l['datum']))
            , $l['tekst']
            , $l['status']
            , $error
        );
    }
    return $botlijst;
}
$cmnds['maallijst'] = array();

/*
 * maalinfo: Informatie weergeven over een bepaalde maaltijd
 *
 * params: maalid
 * uitvoer: 
 *
 */
function maalinfo($uid, $params) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $result = array();

    # als maalid 0 is, eerstvolgende maaltijd zoeken...
    $maalid = $_GET['maalid'];
    if ($maalid == 0) {
        $nu=time(); $lijst = $maaltrack->getMaaltijden($nu-7200, $nu+MAALTIJD_LIJST_MAX_TOT);
        if (count($lijst) > 0) $maalid = $lijst[0]['id'];
        else $result[] = "Er is binnenkort geen maaltijd";
    }
    if ($maaltrack->isMaaltijd($maalid)) {
        $maalinfo = $maaltrack->getMaaltijd($maalid);
        $result[] = "maalid: " . $maalinfo['id'];
        $result[] = "datum: " . str_replace('  ',' ',strftime("%a %e %b '%y %H:%M", $maalinfo['datum']));
        $result[] = "omschrijving: " . $maalinfo['tekst'];
        $result[] = "abosoort: " . $maaltrack->getAboTekst($maalinfo['abosoort']);
        $result[] = sprintf("aantal inschrijvingen: %d/%d%s%s"
            ,$maalinfo['aantal']
            ,$maalinfo['max']
            ,($maalinfo['max'] <= $maalinfo['aantal']) ? ' (VOL)' : ''
            ,($maalinfo['gesloten']) ? ' (GESLOTEN)' : ''
        );
		$lid=new Lid($maalinfo['tp']);
		$result[] = "tafelpraeses: " . $lid->getNaam();
    } else {
        $result[] = "De opgegeven maaltijd bestaat niet.";
    }

    return $result;
}
$cmnds['maalinfo'] = array ('maalid' => true);

/*
 * maalaan: Zichzelf of iemand anders aanmelden voor een maaltijd
 * 
 * params: maalid, proxyuid
 * uitvoer: ['melding of het gelukt is of niet etc...']
 *
 */
function maalaan($uid, $params) {
    return maalaanaf($uid, $params, 'aan');
}
$cmnds['maalaan'] = array ('maalid' => true, 'proxyuid' => false);

/*
 * maalaf: Afmelden voor een maaltijd
 *
 * params: maalid, proxyuid
 * uitvoer: ['melding of het gelukt is of niet etc...']
 *
 */
function maalaf($uid, $params) {
    return maalaanaf($uid, $params, 'af');
}
$cmnds['maalaf'] = array ('maalid' => true, 'proxyuid' => false);

#################################################################################
# afhandelen functieaanroep voor een commando
#

if (array_key_exists($fn,$cmnds)) {
    $params = array();
    foreach ($cmnds[$fn] as $param => $mandatory ) {
        if ($mandatory and !isset($_GET[$param])) {
            echo 'ERROR: Missing ' . $param;
            exit(1);
        } elseif (!$mandatory and !isset($_GET[$param])) {
            $params[$param] = '';
        } else {
            $params[$param] = trim($_GET[$param]);
        }
    }
    echo json_encode($fn($uid, $params));
} else {
    echo 'ERROR: Undefined function';
    exit(1);
}
exit(0);

#################################################################################
# extra helper-functies
#

function maalaanaf($uid, $params, $aanaf) {
    global $lid,$db;
    require_once('maaltijden/class.maaltrack.php');
    $maaltrack = new MaalTrack();
    $proxyuid = ($params['proxyuid'] == $uid or $params['proxyuid'] == '') ? '' : $params['proxyuid'];

    # als maalid 0 is, eerstvolgende maaltijd zoeken... als die er niet is, exit
    $maalid = $params['maalid'];
    if ($maalid == 0) {
        $nu=time(); $lijst = $maaltrack->getMaaltijden($nu-7200, $nu+MAALTIJD_LIJST_MAX_TOT);
        if (count($lijst) > 0) $maalid = $lijst[0]['id'];
        else return array(sprintf("Er is binnenkort geen maaltijd om u voor %s te melden", $aanaf));
    }
    if ($params['proxyuid'] != '' and !$lid->hasPermission('P_MAAL_WIJ'))
        return array(sprintf("U heeft geen rechten om iemand anders voor deze maaltijd %s te melden", $aanaf));
    # lukt het aan/afmelden?
    $fn = $aanaf . 'melden';
    if ($maaltrack->$fn($maalid, $params['proxyuid'])) {
        $maalinfo = $maaltrack->getMaaltijd($maalid);
        return array(sprintf("%s %sgemeld voor de maaltijd op %s"
            , ($params['proxyuid'] == '') ? 'U bent' : LidCache::getLid($proxyuid)->getNaam()
            , $aanaf
            , str_replace('  ',' ',strftime("%a %e %b '%y %H:%M", $maalinfo['datum']))
        ));
    }
    # zo niet, dan foutmelding teruggeven
    if ($proxyuid != '') return array($maaltrack->getProxyError());
    else return array($maaltrack->getError());
}

function anderprofiel($getuid) {
    global $lid;
    # profiel opvragen van iemand anders mag ook, mits...
    # er permissie is om profiel van anderen in te zien en de andere uid bestaat
    if ( !$lid->hasPermission('P_LEDEN_READ') and !$lid->hasPermission('P_OUDLEDEN_READ')
         or !Lid::exists($getuid) ) return false;
    require_once('lid/class.profiel.php');
    $anderlid =LidCache::getLid($getuid);
    if ($anderlid instanceof Lid){
		return $anderlid->getProfiel();
	}
	return false;
}

# Deze functie klust een array met daarin wat informatie uit het profiel.
# Door de bot kan deze vervolgens bijv. in ievent.reply gestopt worden.
function profiel_to_botarray($profiel) {
    $result = array();

    $result[] = sprintf("%s %s%s (%s)"
        , $profiel['voornaam']
        , ($profiel['tussenvoegsel'] != '') ? $profiel['tussenvoegsel'] . ' ' : ''
        , $profiel['achternaam']
        , $profiel['uid']
    );

    $result[] = sprintf("%s %s %s"
        , $profiel['adres']
        , $profiel['postcode']
        , $profiel['woonplaats']
    );

    if ($profiel['telefoon'] != '') $result[] = $profiel['telefoon'];
    if ($profiel['mobiel'] != '') $result[] = $profiel['mobiel'];

    $result[] = sprintf("Moot %s", $profiel['moot']);
    $result[] = sprintf("Kring %s.%s", $profiel['moot'], $profiel['kring']);

    if ($profiel['email'] != '') $result[] = $profiel['email'];
    if ($profiel['studie'] != '') $result[] = sprintf("%s (%s)", $profiel['studie'], $profiel['studiejaar']);
    $result[] = sprintf("lichting %s", $profiel['lidjaar']);

    return $result;
}

# vim:ts=4:sw=4:expandtab

?>
