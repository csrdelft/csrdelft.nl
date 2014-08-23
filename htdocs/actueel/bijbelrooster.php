<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bijbelrooster.php
# -------------------------------------------------------------------
# Bijbelrooster.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'bijbelrooster.class.php';
$inhoud = new Bijbelrooster();

$pagina = new CsrLayoutPage($inhoud);
$pagina->view();
