<?php
require_once 'configuratie.include.php';
require_once 'controller/Barsysteem.class.php';

$barsysteem = new Barsysteem();

if ($barsysteem->isLoggedIn() && isset($_SERVER["PATH_INFO"])) {

    $apiQuery = explode("/", substr($_SERVER["PATH_INFO"], 1));
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    /* Start beheer */
    if ($barsysteem->isAdmin() && $apiQuery[0] == "admin" && count($apiQuery) > 1) {
        switch ($apiQuery[1]) {
            // GET: /admin/ledger
            case "ledger":
                echo json_encode($barsysteem->getLedgerInput());
                break;

            // GET: /admin/tools
            case "tools":
                echo json_encode($barsysteem->getToolData());
                break;

            // POST: /admin/product
            // PUT: /admin/product/:id
            case "product":
                switch($requestMethod) {
                    // POST: /admin/product
                    // Add a new product
                    case "POST":
                        if (isset($_POST['name'], $_POST['price'], $_POST['ledgerId'])) {
                            echo $barsysteem->addProduct($_POST['name'], $_POST['price'], $_POST['ledgerId']);
                        }
                        break;
                    // PUT: /admin/product/:id
                    // Update the price or visibility of a product
                    case "PUT":
                        if (count($apiQuery) == 3) {
                            if (isset($_POST['price'])) {
                                echo $barsysteem->updatePrice($apiQuery[2], $_POST['price']);
                            } elseif (isset($_POST['visibility'])) {
                                echo $barsysteem->updateVisibility($apiQuery[2], $_POST['visibility']);
                            }
                        }
                        break;
                    default:
                }
                break;

            // POST: /admin/account
            // DELETE: /admin/account/:id
            case "account":
                if ($requestMethod == "POST") {
                    echo $barsysteem->addAccount($_POST['name'], $_POST['balance'], $_POST['profileUID']);
                } elseif ($requestMethod == "DELETE" && count($apiQuery) == 3) {
                    echo $barsysteem->removeAccount($apiQuery);
                }
                break;
            default:
        }
    }

    /* Einde beheer */

    switch ($apiQuery[0]) {
        // GET: /accounts
        case "accounts":
            echo json_encode($barsysteem->getAccounts());
            break;

        // GET: /account/:id
        case "account":
            if (count($apiQuery) == 2) {
                switch ($requestMethod) {
                    case "GET":
                        echo json_encode($barsysteem->getAccount($apiQuery[1]));
                        break;
                    case "PUT":
                        echo $barsysteem->updateAccount($apiQuery[1], $_POST['name']);
                        break;
                    default:
                }
            }
            break;

        // GET: /products
        case "products":
            echo json_encode($barsysteem->getProducts());
            break;

        // GET: /orders/:accountId
        case "orders":
            if (count($apiQuery) == 2 && $requestMethod == "GET") {
                echo json_encode($barsysteem->getLatestOrders(
                    $apiQuery[1],
                    isset($_GET["begin"]) ? $_GET["begin"] : "",
                    isset($_GET["eind"]) ? $_GET["eind"] : "",
                    isset($_GET['productType']) ? $_GET['productType'] : array()
                ));
            }
            break;

        // POST: /order
        // DELETE: /order/:id
        // PUT: /order/:id
        case "order":
            switch ($requestMethod) {
                // POST: /order
                // Add new order
                case "POST":
                    if (isset($_POST["order"])) {
                        $data = json_decode($_POST["order"]);
                        if (property_exists($data, "previousOrder")) {
                            $barsysteem->log('update', $_POST);
                            echo $barsysteem->updateOrder($data);
                        } else {
                            $barsysteem->log('insert', $_POST);
                            echo $barsysteem->processOrder(json_decode($_POST["order"]));
                        }
                    }
                    break;

                // DELETE: /order/:id
                // Delete specified order
                case "DELETE":
                    if (count($apiQuery) == 2) {
                        $barsysteem->log('remove', $apiQuery + array("requestMethod" => $requestMethod));
                        echo $barsysteem->verwijderBestelling(json_decode($apiQuery[1]));
                    }
                    break;

                // PUT: /order/:id
                // Restore previously deleted order
                case "PUT":
                    if (count($apiQuery) == 2) {
                        $barsysteem->log('remove', $apiQuery + array("requestMethod" => $requestMethod));
                        echo $barsysteem->undoVerwijderBestelling(json_decode($apiQuery[1]));
                    }
                    break;
                default:
            }
            break;
        default:
    }
}
