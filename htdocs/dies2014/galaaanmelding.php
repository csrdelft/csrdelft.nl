<?php
require_once 'configuratie.include.php';

require_once 'formulier.class.php';

require_once 'diesAanmelding.class.php';

$data=array();
$ingelogd=true;
if($loginlid->instance()->getUid()=='x999'){
	$ingelogd=false;
}else{
	$dies = new DiesAanmelding($loginlid->getUid());

	if($_POST){
		$data=$_POST;
		
		$dies->setData($data['naamDate'],$data['eetZelf'],$data['eetDate'],$data['allerZelf'],$data['allerDate'],$data['date18']);
	}
	else{
		if($dies->filledInBefore()){
			$data=$dies->getData();
		}
		else{
			$data['eetZelf'] = 0;
			$data['allerZelf'] = '';
			$data['naamDate']= '';
			$data['eetDate'] = 0;
			$data['allerDate'] = '';
			$data['date18'] = 0;
		}
	}
}
?>
<html>
<head>
<SCRIPT LANGUAGE="JavaScript">
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

<link rel="stylesheet" type="text/css" href="styles.css">
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
<div id="stekknop"><a href="http://www.csrdelft.nl">
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
if($ingelogd){
               ?>
              <h4>Aanmelding gala 21 februari 2014</h4>
              <p>Met behulp van dit formulier kunt u zich aanmelden voor het Dies Natalis gala der Civitas Studiosorum Reformatorum
              op 21 februari 2014. U dient hier de gegevens van u en uw Diesdame of -heer in te vullen. 
              Door u aan te melden gaat u akkoord met het betalen van twee galakaartjes d.m.v. een machtiging (wilt u geen machtiging dan dient u contact op te nemen met de DiesCie).
              Ook gaat u akkoord met de gedragsregels zoals gespecificeerd in de etiquette en de statuten.</p>
              <?php
              
$eetopties=array('vlees','vis','vegatarisch');
$leeftijdopties=array('nee','ja');

$form=array();

$form[]=new Comment('Eigen gegevens:');
$form[]=new SelectField('eetZelf',$data['eetZelf'],'uw eigen eetvoorkeur',$eetopties);
$form[]=new InputField('allerZelf', $data['allerZelf'], 'allergie-informatie:');
$form[]=new Comment('Gegevens diesdame of -heer:');
$form[]=new InputField('naamDate', $data['naamDate'], 'Naam van uw diesdame of diesheer:');
$form[]=new SelectField('eetDate',$data['eetDate'],'eetvoorkeur van uw diesdame of diesheer',$eetopties);
$form[]=new InputField('allerDate', $data['allerDate'], 'allergie-informatie van uw diesdame of diesheer:');
$form[]=new SelectField('date18', $data['date18'], 'Is uw diespartner 18 jaar of ouder op de dag van het gala:', $leeftijdopties);
$form[]=new SubmitButton('opslaan', '<a class="knop" href="/dies">Annuleren</a>');


$form=new Formulier('galaaanmelding', '/dies2014/galaaanmelding.php', $form);
$form->view();
}
else{
	?>
    <h4>Aanmelding gala 21 februari 2014</h4>
		<h2>U bent niet ingelogd</h2>
		<p>Als u lid bent van C.S.R. Delft dan dient u eerst in te loggen op de webstek. Daarna kan u zich aanmelden voor het gala.</p>
		<p>Als u geen lid bent van C.S.R. Delft maar toch naar het gala wilt dan verzoeken wij u contact op te nemen met ons galadatingbureau 'Onder de Panne'. Dit kan u doen door naar <A HREF="http://www.sonnenvanck.com/onderdepanne2014">deze</A> site te gaan of te mailen naar <A HREF="mailto:onderdepanne@sonnenvanck.com">dit</A> e-mail adres.</p>
		<?php
}
?>

<h5 align="center">Gemaakt door DiesCie der C.S.R. Delft | Voor vragen neem contact op met de <A HREF="mailto:diescie@csrdelft.nl">DiesCie</A></h5>
</div>

</body>
</html>