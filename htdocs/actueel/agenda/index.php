<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontroller.php
# -------------------------------------------------------------------
# Agenda.
# -------------------------------------------------------------------

require_once('include.config.php');

require_once('agenda/class.agenda.php');
require_once('agenda/class.agendacontent.php');
require_once('agenda/class.agendacontroller.php');

$controller=new AgendaController($_GET['query']);
$pagina=new csrdelft($controller->getContent());
$pagina->setZijkolom(false);
$pagina->addStylesheet('agenda.css');
$pagina->view();

?>