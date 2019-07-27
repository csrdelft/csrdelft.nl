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
@define('ROUTES_CACHE_PATH', DATA_PATH . 'routes/');
@define('CONFIG_CACHE_PATH', DATA_PATH . 'config_cache/');
@define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');
@define('TEMPLATE_PATH', BASE_PATH . 'resources/views/');
@define('BLADE_CACHE_PATH', DATA_PATH . 'blade/');
@define('CONFIG_PATH', LIB_PATH . 'config');
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

# Permissies, niet aanpasbaar door defines.include.php
define('P_PUBLIC', 'P_PUBLIC');
define('P_LOGGED_IN', 'P_LOGGED_IN');
define('P_ADMIN', 'P_ADMIN');
define('P_VERJAARDAGEN', 'P_VERJAARDAGEN');
define('P_PROFIEL_EDIT', 'P_PROFIEL_EDIT');
define('P_LEDEN_READ', 'P_LEDEN_READ');
define('P_OUDLEDEN_READ', 'P_OUDLEDEN_READ');
define('P_LEDEN_MOD', 'P_LEDEN_MOD');
define('P_FORUM_READ', 'P_FORUM_READ');
define('P_FORUM_POST', 'P_FORUM_POST');
define('P_FORUM_MOD', 'P_FORUM_MOD');
define('P_FORUM_BELANGRIJK', 'P_FORUM_BELANGRIJK');
define('P_FORUM_ADMIN', 'P_FORUM_ADMIN');
define('P_AGENDA_READ', 'P_AGENDA_READ');
define('P_AGENDA_ADD', 'P_AGENDA_ADD');
define('P_AGENDA_MOD', 'P_AGENDA_MOD');
define('P_DOCS_READ', 'P_DOCS_READ');
define('P_DOCS_POST', 'P_DOCS_POST');
define('P_DOCS_MOD', 'P_DOCS_MOD');
define('P_ALBUM_READ', 'P_ALBUM_READ');
define('P_ALBUM_DOWN', 'P_ALBUM_DOWN');
define('P_ALBUM_ADD', 'P_ALBUM_ADD');
define('P_ALBUM_MOD', 'P_ALBUM_MOD');
define('P_ALBUM_DEL', 'P_ALBUM_DEL');
define('P_BIEB_READ', 'P_BIEB_READ');
define('P_BIEB_EDIT', 'P_BIEB_EDIT');
define('P_BIEB_MOD', 'P_BIEB_MOD');
define('P_NEWS_POST', 'P_NEWS_POST');
define('P_NEWS_MOD', 'P_NEWS_MOD');
define('P_NEWS_PUBLISH', 'P_NEWS_PUBLISH');
define('P_MAAL_IK', 'P_MAAL_IK');
define('P_MAAL_MOD', 'P_MAAL_MOD');
define('P_MAAL_SALDI', 'P_MAAL_SALDI');
define('P_CORVEE_IK', 'P_CORVEE_IK');
define('P_CORVEE_MOD', 'P_CORVEE_MOD');
define('P_CORVEE_SCHED', 'P_CORVEE_SCHED');
define('P_MAIL_POST', 'P_MAIL_POST');
define('P_MAIL_COMPOSE', 'P_MAIL_COMPOSE');
define('P_MAIL_SEND', 'P_MAIL_SEND');
define('P_PEILING_VOTE', 'P_PEILING_VOTE');
define('P_PEILING_EDIT', 'P_PEILING_EDIT');
define('P_PEILING_MOD', 'P_PEILING_MOD');
define('P_FISCAAT_READ', 'P_FISCAAT_READ');
define('P_FISCAAT_MOD', 'P_FISCAAT_MOD');
define('P_ALBUM_PUBLIC_READ', 'P_ALBUM_PUBLIC_READ');
define('P_ALBUM_PUBLIC_DOWN', 'P_ALBUM_PUBLIC_DOWN');
define('P_ALBUM_PUBLIC_ADD', 'P_ALBUM_PUBLIC_ADD');
define('P_ALBUM_PUBLIC_MOD', 'P_ALBUM_PUBLIC_MOD');
define('P_ALBUM_PUBLIC_DEL', 'P_ALBUM_PUBLIC_DEL');
