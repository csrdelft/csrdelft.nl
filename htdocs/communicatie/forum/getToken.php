<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Weergave van categorieën en het forumoverzicht
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');

echo CSR_SERVER.'/communicatie/forum/rss/'.$lid->getToken().'.xml';
?>
