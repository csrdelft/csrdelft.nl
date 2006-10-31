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

# padnaam zonder trailing slash
define('ETC_PATH', '/srv/www/www.csrdelft.nl/etc');
define('LIB_PATH', '/srv/www/www.csrdelft.nl/lib');
define('DATA_PATH', '/srv/www/www.csrdelft.nl/data');
define('PICS_PATH', '/srv/www/www.csrdelft.nl/images');
define('HTDOCS_PATH', '/srv/www/www.csrdelft.nl/htdocs');
define('TMP_PATH', '/srv/www/www.csrdelft.nl/tmp');

# urls met trailing slash
define('CSR_PICS', 'http://plaetjes.csrdelft.nl/');
define('CSR_ROOT','http://pubcie.csrdelft.nl/');

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
define('STATISTICUS', '0217' );
//Feut ip voor de rss feed in #csrdelft
define('FEUT_IP', '145.94.154.180');

//stapeltje dingen includeren die toch (bijna) altijd nodig zijn:
require_once('include.common.php');
require_once('class.lid.php');
require_once('class.mysql.php');

require_once('class.simplehtml.php');
require_once('class.kolom.php');
require_once('class.includer.php');
require_once('class.csrdelft.php');

//smarty template engine...
define('SMARTY_DIR', LIB_PATH.'/smarty/libs/');
define('SMARTY_TEMPLATE_DIR', LIB_PATH.'/templates/');
define('SMARTY_COMPILE_DIR', DATA_PATH.'/smarty/compiled/');
define('SMARTY_CACHE_DIR', DATA_PATH.'/smarty/cache/');

require_once('class.csrsmarty.php');

session_start();
$db = new MySQL();
$lid = new Lid($db);


?>
