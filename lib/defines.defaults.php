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
if (!defined('DB_CHECK')) define('DB_CHECK', false); # zie PersistentEntity::checkTable()
#
# database automatisch bijwerken
if (!defined('DB_MODIFY')) define('DB_MODIFY', false); # heb je een backup gemaakt?
#
# database automatisch droppen
if (!defined('DB_DROP')) define('DB_DROP', false); # heb je een backup gemaakt?
#
# debug modus
if (!defined('DEBUG')) define('DEBUG', false);
#
# onderhoud modus
if (!defined('ONDERHOUD')) define('ONDERHOUD', false);
#
# measure time
if (!defined('TIME_MEASURE')) define('TIME_MEASURE', false);
#
# redirect to https
if (!defined('FORCE_HTTPS')) define('FORCE_HTTPS', true);

# wordt gebruikt voor secure cookies
if (!defined('CSR_DOMAIN')) throw new Exception('CSR_DOMAIN niet gezet.');

# urls ZONDER trailing slash
if (!defined('CSR_ROOT')) define('CSR_ROOT', 'https://' . CSR_DOMAIN);

# Toegestane API origins
if (!defined('API_ORIGINS')) define('API_ORIGINS', 'http://localhost:8080,https://csrdelft.github.io');

# paden MET trailing slash
if (!defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/../') . '/'); # Zet naar absoluut path in je eigen omgeving
if (!defined('ETC_PATH')) define('ETC_PATH', BASE_PATH . 'etc/');
if (!defined('DATA_PATH')) define('DATA_PATH', BASE_PATH . 'data/');
if (!defined('SESSION_PATH')) define('SESSION_PATH', BASE_PATH . 'sessie/');
if (!defined('TMP_PATH')) define('TMP_PATH', BASE_PATH . 'tmp/');
if (!defined('LIB_PATH')) define('LIB_PATH', BASE_PATH . 'lib/');
if (!defined('HTDOCS_PATH')) define('HTDOCS_PATH', BASE_PATH . 'htdocs/');
if (!defined('PHOTOS_PATH')) define('PHOTOS_PATH', HTDOCS_PATH . 'plaetjes/');
if (!defined('PHOTOALBUM_PATH')) define('PHOTOALBUM_PATH', DATA_PATH . 'foto/');
if (!defined('PASFOTO_PATH')) define('PASFOTO_PATH', DATA_PATH . 'foto/pasfoto/');
if (!defined('PUBLIC_FTP')) define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');
if (!defined('TEMPLATE_PATH')) define('TEMPLATE_PATH', BASE_PATH . 'resources/views/');
if (!defined('BLADE_CACHE_PATH')) define('BLADE_CACHE_PATH', DATA_PATH . 'blade/');

# smarty template engine
if (!defined('SMARTY_PLUGIN_DIR')) define('SMARTY_PLUGIN_DIR', LIB_PATH. 'smarty_plugins');
if (!defined('SMARTY_TEMPLATE_DIR')) define('SMARTY_TEMPLATE_DIR', LIB_PATH . 'templates/');
if (!defined('SMARTY_COMPILE_DIR')) define('SMARTY_COMPILE_DIR', DATA_PATH . 'smarty/compiled/');
if (!defined('SMARTY_CACHE_DIR')) define('SMARTY_CACHE_DIR', DATA_PATH . 'smarty/cache/');

# ImageMagick ('magick' voor v7, 'convert' voor v6)
if (!defined('IMAGEMAGICK')) define('IMAGEMAGICK', 'magick');

# BladeOne
# - gebruik MODE_AUTO = 0 voor normale development
# - gebruik MODE_SLOW = 1 als je grote veranderingen maakt
# - gebruik MODE_FAST = 2 in productie
if (!defined('BLADEONE_MODE')) define('BLADEONE_MODE', 0);
