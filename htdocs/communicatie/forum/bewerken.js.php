<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# jsbericht.php
# -------------------------------------------------------------------
# Geeft een berichtbewerkvakje terug in een javascriptje.
# -------------------------------------------------------------------

require_once('include.config.php');

//inhoud
require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();
//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forum->loadByPostID($iPostID);
	
	//post ophalen
	$post=$forum->getSinglePost($iPostID);
	
	//voor javascript de newlines eruit slopen.
	$jssafePost=htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), '\n', addslashes($post['tekst'])), ENT_QUOTES);
	
	//aantal regels voor het invoerveldje bepalen
	$regels=ceil(6+substr_count($jssafePost, '\n')*1.5);
		
	//eventueel een al bestaand formulier wegmikken.
	echo "if(document.getElementById('forumEditForm')){ restorePost(); }";
	
	//het block-element met daarin de post in een object stoppen
	echo "div = document.getElementById('post".$iPostID."');";
	
	//inhoud van de td opslaan voor als we besluiten niet verder te gaan met bewerken
	echo "divContents=div.innerHTML;";
	
	//klein javascriptje om de post eventuele weer terug te zetten
	echo "\nfunction restorePost(){
		div.innerHTML=divContents;
		document.getElementById('forumBericht').disabled=false;
		document.getElementById('forumOpslaan').disabled=false;
		document.getElementById('forumVoorbeeld').disabled=false;
	}\n";
	
	if($forum->magBewerken($iPostID)){
		?>
		
		var editForm='<form action="/communicatie/forum/bewerken/<?php echo $iPostID ?>" method="post" id="forumEditForm">';
		editForm +='<h3>Bericht bewerken</h3>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]<br />';
		editForm +='<div id="berichtPreviewContainer" class="verborgen"><h3>Voorbeeld van uw bericht:</h3><div id="berichtPreview"></div></div>';
		editForm +='<textarea name="bericht" id="forumBewerkBericht" class="tekst" rows="<?php echo $regels; ?>" cols="80" style="width: 100%;">';
		editForm +='<?php echo $jssafePost ?></textarea>';
		editForm +='Reden van bewerking: <input type="text" name="reden" style="width: 250px;"/><br /><br />';
		editForm +='<input type="submit" value="opslaan" /> <input type="button" value="voorbeeld" onclick="previewPost()" /> <input type="button" value="terug" onclick="restorePost()" />';
		editForm +='&nbsp;&nbsp;<a class="knop" onclick="vergrootTextarea(\'forumBewerkBericht\', 10)" title="Vergroot het invoerveld">Invoerveld vergroten</div></form>'
		<?php
	}else{
		?>
		var editForm='<div id="melding"><h2>Foutmelding:</h2>U mag deze post niet bewerken.<br />';
		editForm+='<br /><input type="button" value="terug" onclick="restorePost()" />';
		editForm+='</div>'; 
		<?php
	}
	
	echo 'div.innerHTML = editForm;';
	exit;	
}
