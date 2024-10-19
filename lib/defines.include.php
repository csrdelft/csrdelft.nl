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
define(
	'API_ORIGINS',
	'http://localhost:8080,https://csrdelft.github.io,http://dev-csrdelft.nl'
);
# paden MET trailing slash
define('BASE_PATH', $_ENV['BASE_PATH'] ?: realpath(__DIR__ . '/../') . '/');
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
define('DECLARATIE_PATH', DATA_PATH . 'declaraties/');

# Permissies
define('P_PUBLIC', 'PUBLIC_ACCESS');
define('P_LOGGED_IN', 'ROLE_LOGGED_IN');
define('P_ADMIN', 'ROLE_ADMIN');
define('P_VERJAARDAGEN', 'ROLE_VERJAARDAGEN');
define('P_PROFIEL_EDIT', 'ROLE_PROFIEL_EDIT');
define('P_LEDEN_READ', 'ROLE_LEDEN_READ');
define('P_OUDLEDEN_READ', 'ROLE_OUDLEDEN_READ');
define('P_LEDEN_MOD', 'ROLE_LEDEN_MOD');
define('P_FORUM_READ', 'PUBLIC_ACCESS');
define('P_FORUM_POST', 'ROLE_FORUM_POST');
define('P_FORUM_MOD', 'ROLE_FORUM_MOD');
define('P_FORUM_BELANGRIJK', 'ROLE_FORUM_BELANGRIJK');
define('P_FORUM_ADMIN', 'ROLE_FORUM_ADMIN');
define('P_AGENDA_READ', 'ROLE_AGENDA_READ');
define('P_AGENDA_ADD', 'ROLE_AGENDA_ADD');
define('P_AGENDA_MOD', 'ROLE_AGENDA_MOD');
define('P_DOCS_READ', 'ROLE_DOCS_READ');
define('P_DOCS_POST', 'ROLE_DOCS_POST');
define('P_DOCS_MOD', 'ROLE_DOCS_MOD');
define('P_ALBUM_READ', 'ROLE_ALBUM_READ');
define('P_ALBUM_DOWN', 'ROLE_ALBUM_DOWN');
define('P_ALBUM_ADD', 'ROLE_ALBUM_ADD');
define('P_ALBUM_MOD', 'ROLE_ALBUM_MOD');
define('P_ALBUM_DEL', 'ROLE_ALBUM_DEL');
define('P_BIEB_READ', 'ROLE_BIEB_READ');
define('P_BIEB_EDIT', 'ROLE_BIEB_EDIT');
define('P_BIEB_MOD', 'ROLE_BIEB_MOD');
define('P_NEWS_POST', 'ROLE_NEWS_POST');
define('P_NEWS_MOD', 'ROLE_NEWS_MOD');
define('P_NEWS_PUBLISH', 'ROLE_NEWS_PUBLISH');
define('P_MAAL_IK', 'ROLE_MAAL_IK');
define('P_MAAL_MOD', 'ROLE_MAAL_MOD');
define('P_MAAL_SALDI', 'ROLE_MAAL_SALDI');
define('P_CORVEE_IK', 'ROLE_CORVEE_IK');
define('P_CORVEE_MOD', 'ROLE_CORVEE_MOD');
define('P_CORVEE_SCHED', 'ROLE_CORVEE_SCHED');
define('P_MAIL_POST', 'ROLE_MAIL_POST');
define('P_MAIL_COMPOSE', 'ROLE_MAIL_COMPOSE');
define('P_MAIL_SEND', 'ROLE_MAIL_SEND');
define('P_PEILING_VOTE', 'ROLE_PEILING_VOTE');
define('P_PEILING_EDIT', 'ROLE_PEILING_EDIT');
define('P_PEILING_MOD', 'ROLE_PEILING_MOD');
define('P_FISCAAT_READ', 'ROLE_FISCAAT_READ');
define('P_FISCAAT_MOD', 'ROLE_FISCAAT_MOD');
define('P_ALBUM_PUBLIC_READ', 'PUBLIC_ACCESS');
define('P_ALBUM_PUBLIC_DOWN', 'ROLE_ALBUM_PUBLIC_DOWN');
define('P_ALBUM_PUBLIC_ADD', 'ROLE_ALBUM_PUBLIC_ADD');
define('P_ALBUM_PUBLIC_MOD', 'ROLE_ALBUM_PUBLIC_MOD');
define('P_ALBUM_PUBLIC_DEL', 'ROLE_ALBUM_PUBLIC_DEL');
