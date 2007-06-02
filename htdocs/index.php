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
	
	# snel naar ding voor leden
	if($lid->hasPermission('P_LOGGED_IN')) {
		$snelnaar='<strong>Ga snel naar</strong><br />
			&raquo; <a href="/intern/csrmail/">C.S.R.-courant</a><br />';
		$zijkolom->add(new stringincluder($snelnaar));
	}
	
	# laatste forumberichten toevoegen aan zijkolom:
	require_once('class.forum.php'); 
	require_once('class.forumcontent.php');
	$forum=new forum($lid, $db);
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
	
	# Komende 10 verjaardagen erbij
	if($lid->hasPermission('P_LOGGED_IN')) {
		require_once('class.verjaardagcontent.php');
		
		$verjaardagcontent=new VerjaardagContent($lid, 'komende10');
		$zijkolom->add($verjaardagcontent);
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
	$nieuws=new nieuws( $db, $lid);
	$body->addObject(new nieuwscontent($nieuws));
	
	# bannertje weergeven...
	$body->add(new Includer('', 'banners.html'));

## pagina weergeven
	$pagina=new csrdelft($body,  $lid, $db);
	$pagina->setZijkolom($zijkolom);
	
	$pagina->view();
?>
