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

$barsysteem = new Barsysteem();

if ($barsysteem->isLoggedIn()){

	// Get grootboekinvoer
	if(isset($_GET['q']) && $_GET['q'] == 'grootboek') {
	
		echo '<h1>Grootboek</h1>';
	
	}

	// Get persons
    if (isset($_POST["personen"])) {
        echo json_encode($barsysteem->getPersonen());
    }

	// Get products
    if (isset($_POST["producten"])) {
        echo json_encode($barsysteem->getProducten());
    }

	// Insert order or update order
    if (isset($_POST["bestelling"])) {
        $data = json_decode($_POST["bestelling"]);
        if (property_exists($data, "oudeBestelling")) {
			$barsysteem->log('update', $_POST);
            echo $barsysteem->updateBestelling($data);
        } else {
			$barsysteem->log('insert', $_POST);
            echo $barsysteem->verwerkBestelling(json_decode($_POST["bestelling"]));
        }
    }
	
	// Get saldo
    if (isset($_POST["saldoSocCieId"])) {
        echo $barsysteem->getSaldo($_POST["saldoSocCieId"]);
    }
	
	// Remove order
    if (isset($_POST["verwijderBestelling"])) {
		$barsysteem->log('remove', $_POST);
        echo $barsysteem->verwijderBestelling(json_decode($_POST["verwijderBestelling"]));
    }
	
	// Load orders
    if (isset($_POST["laadLaatste"])) {
        echo json_encode($barsysteem->getBestellingLaatste($_POST["aantal"], $_POST["begin"], $_POST["eind"]));
    }
}
?>
