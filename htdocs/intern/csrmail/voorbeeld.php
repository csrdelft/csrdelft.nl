<?php
# instellingen & rommeltjes
require_once('include.config.php');

# als er genoeg rechten zijn een preview van de csrmail laten zien.
if (!$lid->hasPermission('P_MAIL_COMPOSE')) { header('location: '.CSR_ROOT); exit; }

require_once('class.csrmail.php');
$csrmail = new Csrmail($lid, $db);
require_once('class.csrmailcontent.php');
require_once('class.csrmailcomposecontent.php');
$pagina = new Csrmailcomposecontent($csrmail);

$pagina->view();


?>
