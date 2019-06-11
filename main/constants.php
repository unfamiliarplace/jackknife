<?php

/*
 * =============================================================================
 * Define Jackknife-related constants.
 * =============================================================================
 */

// Directory & URL
define('JKN_BASENAME'       , plugin_basename(JKN_FILE));
define('JKN_DIR'            , plugin_dir_path(JKN_FILE));
define('JKN_URL'            , plugin_dir_url(JKN_FILE));
define('JKN_ASSETS'         , sprintf('%sassets', JKN_URL));

// General
define('JKN_ID'             , 'jkn');
define('JKN_HOOK'           , 'jkn_register');
define('JKN_ICON_URL'       , sprintf('%s/images/menu-icon.png', JKN_ASSETS));

// Cache-related
define('JKN_CACHE_ROOT_EXT' , sprintf('%s/cache', WP_CONTENT_URL));
define('JKN_CACHE_ROOT_INT' , sprintf('%s/cache', WP_CONTENT_DIR));
define('JKN_CACHE_DIR_EXT'  , sprintf('%s/%s', JKN_CACHE_ROOT_EXT, JKN_ID));
define('JKN_CACHE_DIR_INT'  , sprintf('%s/%s', JKN_CACHE_ROOT_INT, JKN_ID));

// Timezone from WP
define('JKN_TIMEZONE'       , get_option('timezone_string'));
