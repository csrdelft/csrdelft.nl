<?php

use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

/**
 * naamlink.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * geeft een naamlink voor een gegeven uid.
 */
if (!LoginModel::mag('P_OUDLEDEN_READ')) {
    echo 'Niet voldoende rechten';
    exit;
}
//is er een uid gegeven?
$given = 'uid';
if (isset($_GET['uid'])) {
    $string = urldecode($_GET['uid']);
} elseif (isset($_POST['uid'])) {
    $string = $_POST['uid'];

//is er een naam gegeven?
} elseif (isset($_GET['naam'])) {
    $string = urldecode($_GET['naam']);
    $given = 'naam';
} elseif (isset($_POST['naam'])) {
    $string = $_POST['naam'];
    $given = 'naam';
} else { //geen input
    echo 'Fout in invoer in tools/naamlink.php';
    exit;
}

//welke subset van leden?
$zoekin = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID', 'S_ERELID');
$toegestanezoekfilters = array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies');
if (isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)) {
    $zoekin = $_GET['zoekin'];
}

function uid2naam($uid) {
    $naam = ProfielModel::getLink($uid, 'civitas');
    if ($naam !== false) {
        return $naam;
    } else {
        return 'Lid[' . htmlspecialchars($uid) . '] &notin; db.';
    }
}

//zoekt uid op en returnt met uid2naam weer de naam
function naam2naam($naam, $zoekin) {
    $rnaam = ProfielService::instance()->zoekLeden($naam, 'naam', 'alle', 'achternaam', $zoekin);
    if ($rnaam) {
        if (isset($rnaam[0]['uid'])) {
            return uid2naam($rnaam[0]['uid']);
        } else {
            if (count($rnaam[0]['naamOpties']) > 0) {
                return 'Meerdere leden mogelijk';
            }
        }
    }
    return 'Geen lid gevonden';
}

if ($given == 'uid') {
    if (AccountModel::isValidUid($string)) {
        echo uid2naam($string);
    } else {
        $uids = explode(',', $string);
        foreach ($uids as $uid) {
            echo uid2naam($uid);
        }
    }
} elseif ($given == 'naam') {
    echo naam2naam($string, $zoekin);
}
