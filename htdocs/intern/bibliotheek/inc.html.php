<?php
	function printReturnHeader() {
?><html>
<head>
    <script type="text/javascript">
        window.onload = function () {
            var divInfoToReturn = document.getElementById("divInfoToReturn");
            parent.returnedData(divInfoToReturn.innerHTML);        
        };
    </script>
</head>
<body>
    <div id="divInfoToReturn"><?
	}
	
	function printReturnFooter() {
?></div>
</body>
</html>
<?
	}
	
	function printHeader($focus = "") {
?><html>
<head>
	<title>Bibliotheek der Civitas</title>
	<link rel="stylesheet" href="scripts/bibliotheek.css" type="text/css" />
	<link rel="stylesheet" href="scripts/modaldbox.css" type="text/css" />
	<link rel="stylesheet" href="scripts/autocomplete.css" type="text/css" />
	<script type="text/javascript" src="scripts/zxml.js"></script>
	<script type="text/javascript" src="scripts/bieb.js"></script>
	<script type="text/javascript" src="scripts/modaldbox.js"></script>
</head>
<body>
	<div id="divBusyIndicator"><img src="images/busy.gif" border="0"></div>
	
	<div id="container">
		
		<div id="header"></div>
		
		<div id="booksbackground"></div>
		
		<div class="navigation_header">
			<ul class="navigation">
				<?php
				// De CSS-klasse om een knop actief te laten lijken.
				$activeString = ' class="active"';
				// De homeknop.
				echo '<li';
				if (iAm('index.php')) { echo $activeString; }
				echo '><a href="index.php">Thuis</a></li>';
				
				// Even lid inladen.
				$lid=Lid::get_lid();
				// De rest v/d knoppen alleen laten zien als het nodig is.
				if($lid->hasPermission('P_BIEB_READ')){
					// De 'Mijn boeken'-knop.
					echo '<li';
					if (iAm('mijnboeken.php')) { echo $activeString; }
					echo '><a href="mijnboeken.php">Mijn boeken</a></li>';
					// Catalogus...
					echo '<li';
					if (iAm('catalogus.php')) { echo $activeString; }
					echo '><a href="catalogus.php">Catalogus</a></li>';
					// En de beheerknop.
					if (gebruikerIsAdmin()) {
						echo '<li';
						if (iAm('beheer.php')) { echo $activeString; }
						echo '><a href="beheer.php">Beheer</a></li>';
					}
				} ?>
			</ul>
		</div>
		
		<? if(!isset($_GET["action"])  && iAm('mijnboeken.php')) { ?>
			<div id="actions">
				<a href="?action=nieuw">Boeken toevoegen</a>
			</div>
		<? } ?>
		<? if (isset($_GET["melding"]) AND $_GET['melding'] != "") { ?>
			<div id="melding">
				<? echo mb_htmlentities($_GET["melding"]); ?>
			</div>
		<? } else  { ?>
			<div id="melding" style="display: none">
			</div>
		<? }?>
		<?
	}
	
	function printFooter() {
		// Deze dialog moet gewoon ergens staan
?>		
		<div id="box" class="dialog">
			<div id="topbalk">
				<div id="dialogKop">Mededeling</div>
				<div id="close" onClick="hm('box');" onMouseOver="closeOver();" onMouseOut="closeOut();"></div>
			</div>
			<div id="dialogContents"></div>
		</div>
		
		<div id="footer">
			<p>Gemaakt door: Ruben Verhaaf</p>
		</div>
	</div>
</body>
</html>
<?
	}
?>