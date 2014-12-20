<?php
include_once('lib/class.imagemap.php');
include_once('lib/include.common.php');

$studentenleven=new Imagemap('studentenleven', 'plaatjes/studentenleven.jpg');
$studentenleven->addArea('studie', 'C.S.R. en Studeren?',  '508,268,508,289,520,318,542,319,639,319,639,310,636,288,622,286,622,272,641,272,641,244,625,242,625,233,518,219,527,230,511,235,511,250,535,252,535,256,546,259,546,263,535,262,535,267', 
'<h1>Studeren</h1>
De combinatie van lid zijn bij C.S.R. en succesvol studeren is heel goed te doen. Je krijgt namelijk een patroon toegewezen. Dat is een ouderejaars die dezelfde studie doet als jij en die jou kan begeleiden. Daarnaast worden activiteiten zo gepland dat ze geen tentamenperiodes in de weg zitten. C.S.R.-leden studeren gemiddeld net zo lang als het Delftse gemiddelde en halen meer dan gemiddeld de eindstreep.');
$studentenleven->addArea('gezelligheid', 'Gezelligheid: borrels en feesten', 
'686,401,688,350,695,340,698,310,711,310,714,340,718,349,716,403,706,401,698,405', 
'<h1>Gezelligheid</h1>
Iedere donderdagavond is er een borrel in de voorzaal van Confide. 
Een relaxte sfeer, mooie gesprekken en gelegenheid om elkaar beter te leren kennen. 
Ook wordt er af en toe een themafeest georganiseerd waar veel vrienden en leden van andere verenigingen aanwezig zijn. 
Ontmoetingsplek bij uitstek zijn de maaltijden. 
Twee keer peer week wordt in de keuken van Confide door leden een goede goedkope maaltijd geserveerd. 
Door het jaar heen zijn er verscheidene weekeinden die in het teken staan van verschillende verbanden, binnen en buiten C.S.R.. 
In Januari is de Dies Natalis, de verjaardag van de vereniging, dit is een week met activiteiten afgesloten met een sprekende climax: het gala.');
$studentenleven->addArea('societeit', 'Sociëteit',
'361,129,364,220,426,222,427,182,423,178,422,164,418,136,416,130,394,128,395,104,391,98,388,83,383,98,380,130',
'<h1>Confide</h1>
<div class="fotoachtergrond"><img src="fotos/th-confide.jpg" alt="Soci&euml;teit Confide" /></div>
Onze, splinternieuwe soci&euml;teit Confide is een prachtige gelegenheid voor allerlei activiteiten. 
Zo\'n vijf jaar geleden heeft C.S.R. dit monumentale pand, een voormalige bioscoop gekocht. 
Deze bioscoop is vervolgens ingrijpend verbouwd tot de plek die het nu is voor C.S.R.. 
Leden van C.S.R. hebben zich van hun beste kant laten zien tijdens de verbouwing: 
studenten Bouwkunde maakten een ontwerp, Civielers rekenden aan de constructie 
en andere leden zetten zich gedurende twee jaar in voor dit resultaat. 
In de gezellige voorzaal met een klassiek interieur vinden de wekelijkse borrels plaats. 
De grotere achterzaal biedt plaats aan grotere gezelschappen en andere activiteiten. 
Zo vinden hier bijvoorbeeld maaltijden, lezingen en feesten plaats. 
Het bestuur heeft een kamer en een platform op de eerste verdieping. 
Een keuken, een commissiekamer, een bibliotheek en archiefruimte maken in Confide van alles mogelijk.');
$studentenleven->addArea('lidworden', 'Lid worden?',
'512,490,643,472,640,468,534,431,421,444,418,444', 
'<h1>Lid worden</h1>
Je kunt lid worden van C.S.R. wanneer je een universitaire- of HBO-opleiding volgt. Als je besloten hebt om lid te worden bij C.S.R. zal de Novitiaats Commissie ervoor zorgen dat je je thuis gaat voelen bij onze vereniging. Deze Commissie bestaat uit oudere jaars die je een half jaar lang alle <em>ins en outs</em> van C.S.R. laten zien.<br />
<br />
Ook als je nu al weet dat je bij C.S.R. lid wil worden kun je je het beste inschrijven <a href="programma.php">tijdens de Owee in ons pand</a>, dus kom zeker een keertje langs, of vul het formulier hieronder in, dan nemen wij contact met je op.<br /><br />
<form action="lidworden.php" method="post" class="studentenleven">
<p>
	<label for="name">Naam</label>
	<input id="name" name="naam" /><br />

	<label for="address">E-mail</label>
	<input id="address" name="email" /><br />

	<label for="city">Telefoonnummer</label>
	<input id="city" name="telefoon" /><br />
	
	<label for="submit"></label>
	<input type="submit" id="submit" name="verzenden" value="verzenden" />
</p>
</form>
');
$studentenleven->addArea('geloof', 'Geloof in je studententijd',
'107,490,175,463,301,481,305,518,285,531,323,540,406,537,405,561,289,556,293,564,294,567,289,573,280,567,277,557,269,546,262,542,247,547,111,524,106,511',
'<h1>Geloof</h1>
<img src="plaatjes/bijbel.gif" style="float: right;" alt="bijbel, NBV" /> 
We zijn enthausiast over de bijbel, Jezus en de dingen die God voor ons in petto heeft. Christen zijn is voor ons dan ook niet enkel een formaliteit. We hebben één keer per twee weken een <em>bijbelkring</em>; deze bestaan uit kleine groepen  van zes tot tien personen. En we hebben werkgroepen waar die je kunt volgen als je wilt. Hier wordt een bepaald onderwerp behandeld of een activiteit georganiseerd. Eens in de maand wordt er een lezing georganiseerd. Een gastspreker vertelt over een interessant onderwerp. 
');
$studentenleven->addArea('fotos', 'Foto\'s',
'331,273,456,271,459,320,331,322',
'<h1>Foto\'s</h1>
Hieronder vind je een aantal foto\'s van activiteiten van C.S.R..
<br /><br />
<strong>Groepsfoto op het jaarlijkse startweekeinde:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-groepsfoto.jpg" alt="Groepsfoto van C.S.R. op het startkamp" /></div>
<strong>Stijlvol en gedegen: het jaarlijkse diesgala:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-gala.jpg"  alt="Bestuur de Jong op het Gala van 2004" /></div>
<strong>Dineren op een C.S.R.-weekeinde:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-sneeuwpret-eten.jpg"  alt="Dineren op sneeuwprêt" /></div>
<strong>Leden vormen zelf een strijkorkest:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-orkest.jpg" alt="Strijkorkest in de achterzaal" /></div>

<strong>C.S.R. leden spelen mee met Lingo</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-lingo.jpg" alt="C.S.R.-leden bij Lingo" /></div>
<strong>Een van de vele C.S.R.-maaltijden</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-startkamp.jpg" alt="Maaltijd op het startkamp" /></div>
<strong>Gezelligheid tijdens een weekeinde:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-weekeinde.jpg" alt="Grappen maken tijdens een C.S.R.-weekeinde" /></div>
<strong>Brak in de badjas:</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-badjas.jpg" alt="Naar Confide in de badjas" /></div>
<strong>C.S.R.-feest in de achterzaal</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-feest.jpg" alt="C.S.R.-feest in de achterzaal" /></div>
<strong>Voorwaar in \'04 op het podium in de achterzaal</strong><br />
<div class="fotoachtergrond"><img src="fotos/th-voorwaar.jpg" alt="Voorwaar in \'04 op het podium in de achterzaal" /></div>

<!--
<strong> </strong><br />
<div class="fotoachtergrond"><img src="fotos/th- .jpg" alt=" " /></div>

-->
 Als je meer foto\'s wilt zien kun je terecht op de <a  href="http://csrdelft.nl/informatie/fotoalbum.php">C.S.R.-webstek</a>.<br />
');
$studentenleven->addArea('organisatie', 'Organistatie: bestuur, commissies',
'116,188,140,177,312,204,314,240,320,239,327,321,324,324,326,386,313,392,310,394,356,389,365,389,424,387,446,397,459,390,480,390,493,404,490,416,464,428,364,450,326,456,298,458,274,455,268,453,230,410,156,413,48,420,46,298,58,270,78,254,102,244,102,205',
'<h1>Organisatie</h1>Ervaring kun je opdoen met commissie- en/of bestuurswerk. Dit is erg handig voor het werk dat je na je studie gaat doen, maar ook goed voor de onderlinge band en je communicatieve vaardigheden. Daarnaast is het gewoon onwijs leuk om iets neer te zetten wat jij organiseert.<br />

Het bestuur van C.S.R. bestaat uit vijf leden van de vereniging. Daarnaast zijn er tal van Commissies, varierend van de BarCommissie tot de AlmanakCommissie.');
$studentenleven->addArea('onderverenigingen', 'Onderverenigingen',
'626,94,638,82,654,74,672,73,688,78,701,91,708,105,711,120,706,137,695,151,682,160,659,164,635,152,622,133,620,111',
'<h1>Onderverenigingen</h1>
Je overtollige energie kan je kwijt in verschillende onderverenigingen. De grootste onderverenigingen zijn Beaufort, Ampel en DéDé. Respectievelijk een zeil-, voetbal- en damesgezelschap. De verschillende onderverenigingen van  C.S.R. zijn zeer actief in het organiseren van allerlei ontspannende of meer serieuze activiteiten. Variërend van strandwandelen, debatteren en sigarenroken tot het spelen in een jazzband.');

/*$studentenleven->addArea('interview', 'Interview met leden',
'509,404,577,410,594,409,581,417,586,424,607,424,618,432,630,429,693,429,704,433,717,433,726,422,724,411,716,404,707,401,693,407,643,416,642,407,615,401,618,386,613,386,608,397,589,405,582,403,586,395,576,396,512,400',
'<h1>Interview met leden</h1>Hier komt een interview met leden.');
*/
$studentenleven->addArea('begin', 'Begin', '0,0,0,0', 
'<h1>Over C.S.R.</h1>
Hoe gaat jouw bureau er komend jaar uitzien? 
Kijk eens rond op dit bureau, en wie weet wat er bij jou volgend jaar op je bureau terecht komt...<br />
Zoals je ziet: <em>"student zijn is méér dan alleen studeren!"</em><br />
Voor een diepgaander verhaal over C.S.R. kan je terecht op <a href="http://csrdelft.nl/informatie/index.php">de website</a>.', true);
		
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | C.S.R. in jouw studentenleven</title>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	<meta name='author' content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name='robots' content='index, follow' />
	<link rel='stylesheet' href='plaatjes/default.css' type='text/css' />
	<style type="text/css">
		<?php echo $studentenleven->getCss(); ?>
	</style>
	<script type="text/javascript">
			<?php echo $studentenleven->getJavascript(); ?>
	</script>
	<script type="text/javascript" src="lib/overlib/overlib.js"></script>
	<script type="text/javascript" src="lib/overlib/overlib_cssw3c.js"></script>
</head>
<body>
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	<div id="oog">
		<a href="index.php" onmouseover="return overlib(\'Terug naar het begin\', CSSW3C);" onmouseout="return nd();">
			<img src="plaatjes/oog.gif" width="100px" style="border: 0px;" title="Terug naar het begin" />
		</a>
	</div>
<?php
	echo $studentenleven->getImagemap(''); 
	oweeCredits();
?>
</body>
</html>
