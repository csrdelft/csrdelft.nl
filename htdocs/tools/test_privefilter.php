<?php
/*
 * test_privefilter.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
 require_once('include.config.php');

 $priveteststring='asdasdf [prive]worst[/prive]dit moet wel blijven![prive=P_LEDEN_READ]kaasworst[/prive] asdfasdfs';

 echo $priveteststring;
 echo '<hr />';
 echo CsrUBB::filterPrive($priveteststring);


?>
