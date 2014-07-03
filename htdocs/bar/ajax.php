<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruben
 * Date: 27-06-14
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */
require_once 'configuratie.include.php';
require_once 'barsysteem.class.php';

if (LoginLid::mag("P_ADMIN")){

    if (isset($_POST["personen"])) {
        $barsysteem = new Barsysteem();
        echo json_encode($barsysteem->getPersonen());
    }

    if (isset($_POST["producten"])) {
        $barsysteem = new Barsysteem();
        echo json_encode($barsysteem->getProducten());
    }

    if (isset($_POST["bestelling"])) {
        $barsysteem = new Barsysteem();
        $data = json_decode($_POST["bestelling"]);
        if ($data->oudeBestelling) {
            echo $barsysteem->updateBestelling($data);
        } else {
            echo $barsysteem->verwerkBestelling(json_decode($_POST["bestelling"]));
        }
    }
    if (isset($_POST["persoonBestellingen"])) {
        $barsysteem = new Barsysteem();
        echo json_encode($barsysteem->getBestellingPersoon($_POST["persoonBestellingen"]));
    }
    if (isset($_POST["saldoSocCieId"])) {
        $barsysteem = new Barsysteem();
        echo $barsysteem->getSaldo($_POST["saldoSocCieId"]);
    }
    if (isset($_POST["verwijderBestelling"])) {
        $barsysteem = new Barsysteem();
        echo $barsysteem->verwijderBestelling(json_decode($_POST["verwijderBestelling"]));
    }
    if (isset($_POST["laadLaatste"])) {
        $barsysteem = new Barsysteem();
        echo json_encode($barsysteem->getBestellingLaatste($_POST["laadLaatste"]));
    }
}
?>
