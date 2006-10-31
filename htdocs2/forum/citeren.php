<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	
# Het middenstuk
if ($lid->hasPermission('P_FORUM_POST')){
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'citeren');
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	
# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
