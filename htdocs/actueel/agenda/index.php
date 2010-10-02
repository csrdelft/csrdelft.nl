<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontroller.php
# -------------------------------------------------------------------
# Agenda.
# -------------------------------------------------------------------

require_once('configuratie.include.php');

require_once('agenda/agenda.class.php');
require_once('agenda/agendacontent.class.php');
require_once('agenda/agendacontroller.class.php');

$controller=new AgendaController($_GET['query']);
$pagina=new csrdelft($controller->getContent());
$pagina->setZijkolom(false);
$pagina->addStylesheet('agenda.css');
$pagina->view();

?>
