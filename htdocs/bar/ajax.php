<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruben
 * Date: 27-06-14
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/controller/Barsysteem.class.php';

$barsysteem = new Barsysteem();

if ($barsysteem->isLoggedIn() && $barsysteem->preventCsrf()){

	/* Start beheer */

	if(isset($_POST["update_person"])) {
		echo $barsysteem->updatePerson($_POST['id'], $_POST['name'])->rowCount();
	}

	if($barsysteem->isBeheer()) {
		// Get grootboekinvoer
		if(isset($_GET['q']) && $_GET['q'] == 'grootboek') {
			echo json_encode($barsysteem->getGrootboekInvoer());
		}
		if(isset($_GET['q']) && $_GET['q'] == 'tools') {
			echo json_encode($barsysteem->getToolData());
		}
		if (isset($_POST["add_product"])) {
			echo $barsysteem->addProduct($_POST['name'], $_POST['price'], $_POST['grootboekId']);
		}
		if (isset($_POST['q']) && $_POST['q'] == 'updatePrice') {
			echo $barsysteem->updatePrice($_POST['productId'], $_POST['price']);
		}
		if(isset($_POST['q']) && $_POST['q'] == 'updateVisibility') {
			echo $barsysteem->updateVisibility($_POST['productId'], $_POST['visibility']);
		}
		if(isset($_POST["add_person"])) {
			echo $barsysteem->addPerson($_POST['name'], $_POST['saldo'], $_POST['uid'])->rowCount();
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
        $data = json_decode((string) $_POST["bestelling"]);
        if (property_exists($data, "oudeBestelling")) {
			$barsysteem->log('update', $_POST);
            echo $barsysteem->updateBestelling($data);
        } else {
			$barsysteem->log('insert', $_POST);
            echo $barsysteem->verwerkBestelling(json_decode((string) $_POST["bestelling"]));
        }
    }

	// Get saldo
    if (isset($_POST["saldoSocCieId"])) {
        echo $barsysteem->getSaldo($_POST["saldoSocCieId"]);
    }

	// Remove order
    if (isset($_POST["verwijderBestelling"])) {
		$barsysteem->log('remove', $_POST);
        echo $barsysteem->verwijderBestelling(json_decode((string) $_POST["verwijderBestelling"]));
    }

	// Undo remove order
    if (isset($_POST["undoVerwijderBestelling"])) {
		$barsysteem->log('remove', $_POST);
        echo $barsysteem->undoVerwijderBestelling(json_decode((string) $_POST["undoVerwijderBestelling"]));
    }

	// Load orders
    if (isset($_POST["laadLaatste"])) {
        echo json_encode($barsysteem->getBestellingLaatste($_POST["aantal"], $_POST["begin"], $_POST["eind"], $_POST['productType'] ?? []));
    }
}
?>
