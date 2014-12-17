<?php

$smarty = CsrSmarty::instance();
$smarty->assign('mainmenu', $wiki->getBody());
$smarty->display('csrdelft/pagina_header.tpl');

echo '<main class="cd-main-content">';
