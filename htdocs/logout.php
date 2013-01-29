<?php

# logout.php

require_once 'configuratie.include.php';

$loginlid->logout();

header('location: '.CSR_ROOT);

exit;

?>
