<?php

header("content-type: text/plain");
define('MODE', 'BOT');

require_once('include.config.php');

# bot-enable in config en params action, user-id is verplicht!
if (!defined('ENABLE_BOT_CMND') or constant('ENABLE_BOT_CMND') !== true or
    !isset($_GET['a']) or !isset($_GET['uid']) or !$lid->login($_GET['uid'])
   ) {
    echo "[]";
    exit(0);
}

$action = $_GET['a'];
$uid = $_GET['uid'];

switch ($action) {
    case 'getuserhosts':
        $profile = $lid->getProfile();
        $userhosts = array();
        if ($profile['msn'] != "") $userhosts[] = $profile['msn'];
        if ($profile['icq'] != "") $userhosts[] = $profile['icq'] . "@login.icq.com";
        if ($profile['jid'] != "") $userhosts[] = $profile['jid'];

        $result = array();
        $result['naam'] = $lid->getCivitasName();
        $result['userhosts'] = $userhosts;

        echo json_encode($result);
        break;

    case 'getsaldo':
        echo json_encode($lid->getSaldi());
        break;

    case 'getabo':
        require_once('class.maaltrack.php');
        $maaltrack = new MaalTrack($lid, $db);
        $myabos = $maaltrack->getAbo();
        if (count($myabos) > 0 )
            echo json_encode(array_values($myabos));
        else
            echo "[]";
        break;

    /*
    case 'getjarig':
        $verjvandaag = $lid->getVerjaardagen(date('n'), date('j'));
        if (count($verjvandaag) > 0) {
            $result = array();
            foreach ($verjvandaag as $verj)
                $result[] = $lid->getNaamLink($verj['uid'], 'full', false, $verj);
            echo json_encode($result);
        } else
            echo "[]";
        break;
    */
    # FIXME: betere verjaardagenopvraagketzer in lid
    case 'getjarig':
        $verj10 = $lid->getKomende10Verjaardagen();
        if (count($verj10) > 0) {
            $result = array();
            foreach ($verj10 as $verj) {
                $naam = $lid->getNaamLink($verj['uid'], 'full', false, $verj);
                $datum = date("j-n", mktime(0,0,0,date('m'),date('j')+$verj['jarig_over']));
                # ugly
                $result[] = sprintf('%s %s (%s)', $datum, $naam, $verj['leeftijd']);
            }
            echo json_encode($result);
        } else
            echo "[]";
        break;
    case 'getprofiel':
        # profiel opvragen van iemand anders mag ook, mits...
        if (isset($_GET['getuid']) and $_GET['getuid'] != $uid) {
            # er permissie is om profiel van anderen in te zien en de andere uid bestaat
            if ( !$lid->hasPermission('P_LEDEN_READ') and !$lid->hasPermission('P_OUDLEDEN_READ')
                 or !$lid->uidExists($_GET['uid']) ) {
                echo "[]";
                break;
            }
            require_once('class.profiel.php');
            $anderlid = new Profiel($db);
            if (!$anderlid->loadSqlTmpProfile($_GET['getuid'])) {
                echo "[]";
                break;
            }
            $profiel = $anderlid->getTmpProfile();
        } else {
            $profiel = $lid->getProfile();
        }

        $result = array();

        $result[] = sprintf("%s %s%s "
            , $profiel['voornaam']
            , ($profiel['tussenvoegsel'] != '') ? $profiel['tussenvoegsel'] . ' ' : ''
            , $profiel['achternaam']
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

        echo json_encode($result);

        break;
    case 'whoami':
        echo json_encode(array($lid->getFullName()));
        break;
    case 'perms':
        echo json_encode(array($lid->getPermissions()));
        break;
    case 'zoekoud':
    case 'zoek':
        if (!isset($_GET['zoekterm'])) {
            echo "[]";
            break;
        }
        $status = ($action == 'zoek') ? 'leden' : 'oudleden';
        $leden = $lid->zoekLeden(urldecode($_GET['zoekterm']), 'naam', 'alle', 'uid', $status);
        # nu array bouwen van naam en uid
        $result = array();
        foreach ($leden as $l)
           $result[] = $l['uid'] . " " . $lid->getNaamLink($l['uid'], 'full', false, $l, false);
        echo json_encode($result);
        break;
    default:
        echo "[]";
}

# vim:ts=4:sw=4:expandtab

?>

