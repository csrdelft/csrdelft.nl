<?php

// Geef de mogelijkheid om defines te overriden.
if (is_file(__DIR__ . '/defines.include.php')) {
    require_once 'defines.include.php';
}

# -------------------------------------------------------------------
# defines.defaults.php
# -------------------------------------------------------------------
# Dit zijn de standaard instellingen, pas ze aan in defines.include.php
# -------------------------------------------------------------------
#
# database automatisch controleren
@define('DB_CHECK', false); # zie PersistentEntity::checkTable()
# database automatisch bijwerken
@define('DB_MODIFY', false); # heb je een backup gemaakt?
# database automatisch droppen
@define('DB_DROP', false); # heb je een backup gemaakt?
# debug modus
@define('DEBUG', false);
# onderhoud modus
@define('ONDERHOUD', false);
# measure time
@define('TIME_MEASURE', false);
# redirect to https
@define('FORCE_HTTPS', true);
# urls ZONDER trailing slash
@define('CSR_ROOT', 'https://' . CSR_DOMAIN);
# Toegestane API origins
@define('API_ORIGINS', 'http://localhost:8080,https://csrdelft.github.io');
# paden MET trailing slash
@define('BASE_PATH', realpath(__DIR__ . '/../') . '/'); # Zet naar absoluut path in je eigen omgeving
@define('ETC_PATH', BASE_PATH . 'etc/');
@define('DATA_PATH', BASE_PATH . 'data/');
@define('SESSION_PATH', BASE_PATH . 'sessie/');
@define('TMP_PATH', BASE_PATH . 'tmp/');
@define('LIB_PATH', BASE_PATH . 'lib/');
@define('HTDOCS_PATH', BASE_PATH . 'htdocs/');
@define('PHOTOS_PATH', HTDOCS_PATH . 'plaetjes/');
@define('PHOTOALBUM_PATH', DATA_PATH . 'foto/');
@define('PASFOTO_PATH', DATA_PATH . 'foto/pasfoto/');
@define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');
@define('TEMPLATE_PATH', BASE_PATH . 'resources/views/');
@define('BLADE_CACHE_PATH', DATA_PATH . 'blade/');
# smarty template engine
@define('SMARTY_PLUGIN_DIR', LIB_PATH. 'smarty_plugins');
@define('SMARTY_TEMPLATE_DIR', LIB_PATH . 'templates/');
@define('SMARTY_COMPILE_DIR', DATA_PATH . 'smarty/compiled/');
@define('SMARTY_CACHE_DIR', DATA_PATH . 'smarty/cache/');
# ImageMagick ('magick' voor v7, 'convert' voor v6)
@define('IMAGEMAGICK', 'magick');
# BladeOne
# - gebruik MODE_AUTO = 0 voor normale development
# - gebruik MODE_SLOW = 1 als je grote veranderingen maakt
# - gebruik MODE_FAST = 2 in productie
@define('BLADEONE_MODE', 0);

# wordt gebruikt voor secure cookies, zonder deze kan niet geleefd worden
if (!defined('CSR_DOMAIN')) {
	throw new Exception('CSR_DOMAIN niet gezet.');
}
