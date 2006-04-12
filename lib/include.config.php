<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# include.config.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
# Historie:
# 18-08-2005 Hans van Kranenburg
# . gemaakt
#

define('ETC_PATH', '/srv/www/www.csrdelft.nl/etc/');
define('LIB_PATH', '/srv/www/www.csrdelft.nl/lib');
define('DATA_PATH', '/srv/www/www.csrdelft.nl/data/');
define('PICS_PATH', '/srv/www/www.csrdelft.nl/htdocs/images');
define('HTDOCS_PATH', '/srv/www/www.csrdelft.nl/htdocs/');
define('TMP_PATH', '/srv/www/www.csrdelft.nl/tmp/');
define('SMARTY_DIR', '/srv/www/www.csrdelft.nl/lib/smarty/');

define('CSR_PICS', 'http://csrdelft.nl/images/');
define('CSR_ROOT','http://csrdelft.nl/');

# We willen geen sessie-id in de url hebben
ini_set('session.use_only_cookies', 1);
session_save_path('/srv/www/www.csrdelft.nl/sessie');

# wat instellingen
ini_set('include_path', LIB_PATH . ':' . ini_get('include_path'));
ini_set('upload_tmp_dir','/srv/www/www.csrdelft.nl/tmp');
setlocale(LC_ALL, 'nl_NL.utf8@euro');

//standaard templaat voor de csrmail
define('CSRMAIL_TEMPLATE', 'csrmail.tpl');

# wordt gebruikt om pagina's alleen op Confide te laten zien
define('CONFIDE_IP', '82.92.101.131:145.94.37.96');

# hoe ver van tevoren worden maaltijden getoond?
define('MAALTIJD_LIJST_MAX_TOT', 86400*7*4);
# hoe lang van tevoren mag je iemand anders voor een maaltijd inschrijven?
define('MAALTIJD_PROXY_MAX_TOT', 86400*2);
# wat is het standaard aantal max inschrijvingen voor een maaltijd?
define('MAX_MAALTIJD', 100);
//verenigingsstatisticus
define('STATISTICUS', '0127' );

?>
