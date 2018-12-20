<?php
/**
 * interesse.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Submit voor interesseformulier
 */

use CsrDelft\common\GoogleCaptcha;
use CsrDelft\model\entity\Mail;

require_once "configuratie.include.php";

$naam = $_POST["naam"];
$email = $_POST["submit_by"];
$adres = $_POST["straat"];
$postcode = $_POST["postcode"];
$woonplaats = $_POST["plaats"];
$telefoon = $_POST["telefoon"];
$opmerking = $_POST["opmerking"];

$interesses = [];

if (isset($_POST["interesse1"])) array_push($interesses, $_POST["interesse1"]);
if (isset($_POST["interesse2"])) array_push($interesses, $_POST["interesse2"]);
if (isset($_POST["interesse3"])) array_push($interesses, $_POST["interesse3"]);
if (isset($_POST["interesse4"])) array_push($interesses, $_POST["interesse4"]);
if (!GoogleCaptcha::verify()) {
    echo "Verzenden mislukt";
    exit;
}

$interessestring = '';
foreach ($interesses as $interesse) $interessestring .= " * " . $interesse . "\n";

$bericht = "
Beste OweeCie,

Het interesseformulier op de stek is ingevuld:

Naam: $naam
Email: $email
Adres: $adres
Postcode: $postcode
Woonplaats: $woonplaats
Telefoon: $telefoon

Interesses:
$interessestring
Opmerking:
$opmerking


Met vriendelijke groeten,
De PubCie.
";

$mail = new Mail(array("oweecie@csrdelft.nl" => "OweeCie", $email => $naam), "Interesseformulier", $bericht);
$mail->setFrom($email);
$mail->send();
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Het is gelukt!</h1>
    <p>Het interesseformulier is verzonden!</p>
    <p><a href="/">Klik hier om terug te gaan</a></p>
</body>
</html>

