<?php

# -------------------------------------------------------------------
# defines.include.php
# -------------------------------------------------------------------
# allerlei paden enzo goed zetten.
# -------------------------------------------------------------------
#
# database automatisch controleren
define('DB_CHECK', true); # zie PersistentEntity::checkTable()
#
# database automatisch bijwerken
define('DB_MODIFY', false); # heb je een backup gemaakt?
#
# database automatisch droppen
define('DB_DROP', false); # heb je een backup gemaakt?
#
# debug modus
define('DEBUG', false);
#
# minify js & css
define('MINIFY', false);
#
# measure time
define('TIME_MEASURE', false);
#
# redirect to https
define('FORCE_HTTPS', false);

# wordt gebruikt om pagina's alleen op Confide te laten zien
#define('CONFIDE_IP', '80.112.180.173');

# wordt gebruikt voor secure cookies
define('CSR_DOMAIN', 'localhost');

# urls ZONDER trailing slash
define('CSR_ROOT', 'http://' . CSR_DOMAIN . ':8080');
define('ASSETS_DIR', CSR_ROOT . '/assets');

# JWT secret key for API
define('JWT_SECRET', 'BjG\0_;,OY5k)w-frmSpgleH"*^6Q_t{M=uh.<:nH8n<Xrs!FZY=TGhi}{)B"Wa');

# JWT lifetime for API, in seconds
define('JWT_LIFETIME', 3600);

# Toegestane API origins
define('API_ORIGINS', 'http://localhost:8080,https://csrdelft.github.io');

define('CAPTCHA_SECRET', '<Zet mij>');
define('GOOGLE_CLIENT_ID', '<Zet mij>');
define('GOOGLE_CLIENT_SECRET', '<Zet mij>');
define('GOOGLE_EMBED_KEY', '<Zet mij>');

# paden MET trailing slash
define('BASE_PATH', '/app/');
define('ETC_PATH', BASE_PATH . 'etc/');
define('DATA_PATH', BASE_PATH . 'data/');
define('SESSION_PATH', BASE_PATH . 'sessie/');
define('TMP_PATH', BASE_PATH . 'tmp/');
define('LIB_PATH', BASE_PATH . 'lib/');
define('HTDOCS_PATH', BASE_PATH . 'htdocs/');
define('PHOTOS_PATH', HTDOCS_PATH . 'plaetjes/');
define('PHOTOALBUM_PATH', DATA_PATH . 'foto/');
define('PASFOTO_PATH', DATA_PATH . 'foto/pasfoto/');
define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');

# smarty template engine
define('SMARTY_PLUGIN_DIR', LIB_PATH. 'smarty_plugins');
define('SMARTY_TEMPLATE_DIR', LIB_PATH . 'templates/');
define('SMARTY_COMPILE_DIR', DATA_PATH . 'smarty/compiled/');
define('SMARTY_CACHE_DIR', DATA_PATH . 'smarty/cache/');

# ImageMagick ('magick' voor v7, 'convert' voor v6)
define('IMAGEMAGICK', 'magick');
