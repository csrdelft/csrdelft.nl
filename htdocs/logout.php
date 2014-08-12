<?php

# logout.php

require_once 'configuratie.include.php';

LoginSession::instance()->logout();

header('location: '.CSR_ROOT);

exit;

?>
