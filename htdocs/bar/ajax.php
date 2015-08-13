<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruben
 * Date: 27-06-14
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */
require_once 'configuratie.include.php';
require_once 'controller/Barsysteem.class.php';

$barsysteem = new Barsysteem();

if ($barsysteem->isLoggedIn()){

	/* Start beheer */
		
	if(isset($_POST["update_person"])) {
		echo $barsysteem->updatePerson($_POST['id'], $_POST['name']);
	}

	if($barsysteem->isBeheer()) {		
		// Get grootboekinvoer
		if(isset($_GET['q']) && $_GET['q'] == 'grootboek') {
			echo json_encode($barsysteem->getGrootboekInvoer());
		}
		if(isset($_GET['q']) && $_GET['q'] == 'tools') {
			echo json_encode($barsysteem->getToolData());
		}
        if(isset($_POST["add_product"])) {
            echo $barsysteem->addProduct($_POST['name'], $_POST['price'], $_POST['grootboekId']);
        }
		if(isset($_POST['q']) && $_POST['q'] == 'updatePrice') {
			echo $barsysteem->updatePrice($_POST['productId'], $_POST['price']);
		}
		if(isset($_POST['q']) && $_POST['q'] == 'updateVisibility') {
			echo $barsysteem->updateVisibility($_POST['productId'], $_POST['visibility']);
		}
		if(isset($_POST["add_person"])) {
			echo $barsysteem->addPerson($_POST['name'], $_POST['saldo'], $_POST['uid']);
		}
		if(isset($_POST["remove_person"])) {
			echo $barsysteem->removePerson($_POST['id']);
		}
	}
	
	/* Einde beheer */

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
	
	// Undo remove order
    if (isset($_POST["undoVerwijderBestelling"])) {
		$barsysteem->log('remove', $_POST);
        echo $barsysteem->undoVerwijderBestelling(json_decode($_POST["undoVerwijderBestelling"]));
    }
	
	// Load orders
    if (isset($_POST["laadLaatste"])) {
        echo json_encode($barsysteem->getBestellingLaatste($_POST["aantal"], $_POST["begin"], $_POST["eind"], isset($_POST['productType']) ? $_POST['productType'] : array()));
    }
}
?>
