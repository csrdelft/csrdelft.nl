<?php
/*
 * mobiel.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'configuratie.include.php';

$_SESSION['pauper']=true;


$body=new Kolom();
require_once 'pagina.class.php';
require_once 'paginacontent.class.php';


$paginacontent = new PaginaContent(new Pagina('mobiel'));
$paginacontent->setActie('bekijken');
$body->addObject($paginacontent);
# Laatste forumberichten
require_once 'forum/forum.class.php');
require_once 'forum/forumcontent.class.php');
$forum=new forum();
$forumcontent=new forumcontent($forum, 'lastposts');
$body->add(new stringincluder('<div class="recent">'));
$body->add($forumcontent);
$body->add(new stringincluder('</div>'));
## pagina weergeven
$pagina=new csrdelft($body);
$pagina->setZijkolom(false);


$pagina->view();
?>
