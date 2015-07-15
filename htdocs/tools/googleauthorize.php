<?php
require_once 'configuratie.include.php';

require_once "Google/autoload.php";

$google_client_id = '833326410856-12693ose5ecsrghrsftmeeu3b02rlevu.apps.googleusercontent.com';
$google_client_secret = '1LwghAmfTdREGHRuL-Gcy7tX';
$google_redirect_uri = 'https://dev.csrdelft.nl/googlecallback';

//setup new google client
$client = new Google_Client();
$client -> setApplicationName('Stek');
$client -> setClientid($google_client_id);
$client -> setClientSecret($google_client_secret);
$client -> setRedirectUri($google_redirect_uri);
$client -> setAccessType('online');
$client -> setScopes('https://www.google.com/m8/feeds');

$googleImportUrl = $client -> createAuthUrl();

//google response with contact. We set a session and redirect back
if (isset($_GET['code'])) {
    $auth_code = $_GET["code"];
    $_SESSION['google_token'] = $auth_code;
    header("Location: " . CSR_ROOT . "/profiel/" . $_GET['state'] . "/addToGoogleContacts");
}