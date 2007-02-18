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
	//laatste forumberichten toevoegen aan zijkolom:
	require_once(LIB_PATH . '/class.forum.php'); 
	require_once(LIB_PATH . '/class.forumcontent.php');
	$forum=new forum($lid, $db);
	$forumcontent=new forumcontent($forum, 'lastposts');
	
	$zijkolom->add($forumcontent);
	$zijkolom->add(new Includer('', 'alpha.html'));

## de pagina-inhoud;
	$body=new kolom();
	$thuis = new Includer('', 'thuis.html');
	$body->addObject($thuis);

	//nieuws.
	require_once(LIB_PATH . '/class.nieuws.php');
	require_once(LIB_PATH . '/class.nieuwscontent.php');
	$nieuws=new nieuws( $db, $lid);
	$body->addObject(new nieuwscontent($nieuws));
	//bannertje weergeven...
	$body->add(new Includer('', 'banners.html'));

## pagina weergeven
$pagina=new csrdelft($body,  $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();

?>
