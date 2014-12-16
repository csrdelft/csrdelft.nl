<?php
require_once 'configuratie.include.php';
require_once 'diesAanmelding.class.php';

$data = array();
$ingelogd = true;
$bericht = '';

if (!LoginModel::mag('P_LOGGED_IN')) {
	$ingelogd = false;
} else {
	$dies = new DiesAanmelding(LoginModel::getUid());
	if ($dies->galaVol()) {
		$bericht = '<h3>Gala is vol</h3><p>Helaas, er zijn inmiddels 100 inschrijvingen voor het gala, daarom is de inschrijving nu gesloten.</p>';
		$ingelogd = false;
	}
	if ($_POST && false) {
		$data = $_POST;
		$dies->setData($data['naamDate'], $data['eetZelf'], $data['eetDate'], $data['allerZelf'], $data['allerDate'], $data['date18']);
		$bericht = '<h3>Aanmelding succesvol opgeslagen</h3>';
	}
	if ($dies->filledInBefore()) {
		$data = $dies->getData();
		$ingelogd = true;
	} else {
		$data['eetZelf'] = 0;
		$data['allerZelf'] = '';
		$data['naamDate'] = '';
		$data['eetDate'] = 0;
		$data['allerDate'] = '';
		$data['date18'] = 0;
	}
}
$ingelogd = false;
?>
<html>
	<head>
		<script language="javascript" type="text/javascript">
			<!-- Begin
			loadImage1 = new Image();
			loadImage1.src = "./knoppen/themafilmpje2.png";
			staticImage1 = new Image();
			staticImage1.src = "./knoppen/themafilmpje1.png";

			loadImage2 = new Image();
			loadImage2.src = "./knoppen/programma2.png";
			staticImage2 = new Image();
			staticImage2.src = "./knoppen/programma1.png";

			loadImage3 = new Image();
			loadImage3.src = "./knoppen/galaaanmelding2.png";
			staticImage3 = new Image();
			staticImage3.src = "./knoppen/galaaanmelding1.png";

			loadImage4 = new Image();
			loadImage4.src = "./knoppen/etiquette2.png";
			staticImage4 = new Image();
			staticImage4.src = "./knoppen/etiquette1.png";

			loadImage5 = new Image();
			loadImage5.src = "./knoppen/commissie2.png";
			staticImage5 = new Image();
			staticImage5.src = "./knoppen/commissie1.png";

			loadImage6 = new Image();
			loadImage6.src = "./knoppen/posters2.png";
			staticImage6 = new Image();
			staticImage6.src = "./knoppen/posters1.png";
			// End -->
		</script>
		<script src="/layout/js/jquery/jquery.min.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="styles.css">
		<script src="/layout/js/csrdelft.js" type="text/javascript"></script>
		<style>
			p {
				padding-right:5%;
			}
		</style>
		<title>Extraordinair - 52e Dies Natalis der C.S.R. Delft</title>
	</head>
	<body>

		<!--div id="background">
			<img src="./Afbeeldingen/achtergrond strip.jpg" class="stretch" alt="" />
		</div-->

		<div id="header">
			<div id="homeknop"><a href="index.html">
					<img src="./Afbeeldingen/knop2_home.png" alt="Home" width="100" height="50"></a>
			</div>
			<div id="stekknop"><a href="http://csrdelft.nl">
					<img src="./Afbeeldingen/knop3_csrstek.png" alt="C.S.R Stek" width="100" height="50"></a>
			</div>
			<img src="./Afbeeldingen/toplogo.jpg" alt="" style="position:absolute;left:50%; top:12px; margin-left:-170px" width="340" height="100">
		</div>

		<div id="content" style="overflow-y:visible">
			<div id="column3">
				<img src="./Afbeeldingen/aanmelding_konijn.png" width="291" height="500" vspace="25" style="position:relative;left:50% ; margin-left:-145px;">
			</div>
			<div id="column4">
				<?php
				echo $bericht;
				if ($ingelogd) {
					?>
					<h4>Aanmelding gala 21 februari 2014</h4>
					<p>Met behulp van dit formulier kunt u zich aanmelden voor het Dies Natalis gala der Civitas Studiosorum Reformatorum op 21 februari 2014. U dient hier de gegevens van u en uw Diesdame of -heer in te vullen. Door u aan te melden gaat u akkoord met het betalen van twee galakaartjes d.m.v. een machtiging (wilt u geen machtiging dan dient u contact op te nemen met de DiesCie).</p>
					<?php
					$eetopties = array('vlees', 'vis', 'vegatarisch');
					$leeftijdopties = array('nee', 'ja');

					$fields[] = new Subkopje('Uw gegevens:');
					$fields[] = new SelectField('eetZelf', $data['eetZelf'], 'Eetvoorkeur', $eetopties);
					$fields[] = new TextField('allerZelf', $data['allerZelf'], 'Allergie-informatie:');
					$fields[] = new Subkopje('Gegevens van uw Diesdame of Diesheer:');
					$fields[] = new TextField('naamDate', $data['naamDate'], 'Naam:');
					$fields[] = new SelectField('eetDate', $data['eetDate'], 'Eetvoorkeur', $eetopties);
					$fields[] = new TextField('allerDate', $data['allerDate'], 'Allergie-informatie:');
					$fields[] = new SelectField('date18', $data['date18'], 'Is uw Diesdame of Diesheer meerderjarig op de dag van het gala?', $leeftijdopties);
					$fields['btn'] = new FormDefaultKnoppen('/dies', true, false);
					$fields['btn']->submitTitle = 'Aanmelding opslaan';

					$form = new Formulier(null, 'galaaanmelding', '/dies/galaaanmelding.php');
					$form->addFields($fields);
					$form->view();
				} else {
					?>
					<h4>Aanmelding gala 21 februari 2014</h4>
					<p>De inschrijving voor het gala is gesloten, wijzigingen doorgeven is ook niet meer mogelijk</p>
					<?php
				}
				?>
			</div>
		</div>

		<div id="footer">
			<table cellspacing="8" align="center">
				<tr>
					<td><a href="themafilmpje.html" onmouseover="image1.src = loadImage1.src;" onmouseout="image1.src = staticImage1.src;">
							<img name="image1" src="./knoppen/themafilmpje1.png" width="154" height="50" border=0></a></td>

					<td><a href="programma.html" onmouseover="image2.src = loadImage2.src;" onmouseout="image2.src = staticImage2.src;">
							<img name="image2" src="./knoppen/programma1.png" width="154" height="50" border=0></a></td>

					<td><a href="galaaanmelding.php">
							<img name="image3" src="./knoppen/galaaanmelding3.png" width="154" height="50" border=0></a></td>
				</tr>
				<tr>
					<td><a href="etiquette.html" onmouseover="image4.src = loadImage4.src;" onmouseout="image4.src = staticImage4.src;">
							<img name="image4" src="./knoppen/etiquette1.png" width="154" height="50" border=0></a></td>

					<td><a href="commissie.html" onmouseover="image5.src = loadImage5.src;" onmouseout="image5.src = staticImage5.src;">
							<img name="image5" src="./knoppen/commissie1.png" width="154" height="50" border=0></a></td>

					<td><a href="posters.html" onmouseover="image6.src = loadImage6.src;" onmouseout="image6.src = staticImage6.src;">
							<img name="image6" src="./knoppen/posters1.png" width="154" height="50" border=0></a></td>
				</tr>
			</table>
			<h5 align="center">Gemaakt door DiesCie der C.S.R. Delft | Voor vragen neem contact op met de <a href="mailto:diescie@csrdelft.nl">DiesCie</a></h5>
		</div>

	</body>
</html>
