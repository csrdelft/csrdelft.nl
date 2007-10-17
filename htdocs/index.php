<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	require_once('class.menu.php');
	$zijkolom->add(new stringincluder(Menu::getGaSnelNaar()));
	
	
	# laatste forumberichten toevoegen aan zijkolom:
	require_once('class.forum.php'); 
	require_once('class.forumcontent.php');
	$forum=new forum();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
	
	# Komende 10 verjaardagen erbij
	if($lid->hasPermission('P_LOGGED_IN')) {
		require_once('class.verjaardagcontent.php');
		
		$zijkolom->add(new VerjaardagContent('komende10'));
	}
	
	# Alpha-reclame
	$zijkolom->add(new Includer('', 'alpha.html'));

## de pagina-inhoud
	$body=new kolom();
	$thuis = new Includer('', 'thuis.html');
	$body->addObject($thuis);

	# nieuws
	require_once('class.nieuws.php');
	require_once('class.nieuwscontent.php');
	$nieuws=new nieuws();
	$body->addObject(new nieuwscontent($nieuws));
	
	# bannertje weergeven...
	$body->add(new Includer('', 'banners.html'));

## pagina weergeven
	$pagina=new csrdelft($body);
	$pagina->setZijkolom($zijkolom);
	
	$pagina->view();
?>
