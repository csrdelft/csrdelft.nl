<?php

# -------------------------------------------------------------------
# defines.include.php
# -------------------------------------------------------------------
# Zet alle defines klaar
# -------------------------------------------------------------------
#
# database automatisch controleren
define('DB_CHECK', false); # zie PersistentEntity::checkTable()
# database automatisch bijwerken
define('DB_MODIFY', false); # heb je een backup gemaakt?
# database automatisch droppen
define('DB_DROP', false); # heb je een backup gemaakt?
# debug modus
define('DEBUG', $_SERVER['APP_DEBUG']);
# measure time
define('TIME_MEASURE', false);
# redirect to https
define('FORCE_HTTPS', $_ENV['FORCE_HTTPS'] == 'true');
# Toegestane API origins
define('API_ORIGINS', 'http://localhost:8080,https://csrdelft.github.io,http://dev-csrdelft.nl');
# paden MET trailing slash
define('BASE_PATH', $_ENV['BASE_PATH'] ? $_ENV['BASE_PATH'] : realpath(__DIR__ . '/../') . '/');
define('ETC_PATH', BASE_PATH . 'etc/');
define('DATA_PATH', BASE_PATH . 'data/');
define('SESSION_PATH', BASE_PATH . 'sessie/');
define('LIB_PATH', BASE_PATH . 'lib/');
define('HTDOCS_PATH', BASE_PATH . 'htdocs/');
define('VAR_PATH', BASE_PATH . 'var/');
define('TMP_PATH', VAR_PATH . 'tmp/');
define('PHOTOS_PATH', HTDOCS_PATH . 'plaetjes/');
define('PHOTOALBUM_PATH', DATA_PATH . 'foto/fotoalbum/');
define('PASFOTO_PATH', DATA_PATH . 'foto/pasfoto/');
define('PLAATJES_PATH', DATA_PATH . 'plaatjes/');
define('CONFIG_CACHE_PATH', VAR_PATH . 'config_cache/');
define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');
define('CONFIG_PATH', BASE_PATH . 'config');
define('TEMPLATE_DIR', LIB_PATH . 'templates/');

# Permissies
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
