<?php

$smarty = CsrSmarty::instance();
$smarty->assign('mainmenu', $wiki->getBody());
$smarty->display('MVC/layout/pagina_header.tpl');

echo '<main class="cd-main-content">';
