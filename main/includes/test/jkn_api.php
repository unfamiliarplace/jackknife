<?php

/*
 * =============================================================================
 * Very quick test of basic module activation.
 * 
 * TODO The test suite is prepreprealpha... it contributes almost nothing.
 * =============================================================================
 */

/*
 * =============================================================================
 * Require the module files and create the space
 * =============================================================================
 */

require_once 'dep_test/module.php';
require_once 'mod_test/module.php';

$space = JKNAPI::create_space('jkntest', 'Jackknife Test');
$space->set_menu_order(82);
$space->set_icon_url(sprintf('%sassets/menu-icon.png', $space->url()));


/*
 * =============================================================================
 * Create a module with a plugin dependency
 * =============================================================================
 */

$mod_with_plugin_dep = new JKNDepTest($space);

/**
 * If you actually run this, you could upload and activate the included
 * test plugin and uncomment the code below.
 */

/**
 * $plugin_dep = new JKNPluginDependency([
 *      'id'    => 'test',
 *      'name'  => 'AAA Test Dep Plugin',
 *      'url'   => 'https://en-ca.wordpress.org/plugins',
 *      'file'  => 'aaa_test_dep_plugin/aaa_test_dep_plugin.php'
 * ]);
 * 
 * $mod_with_plugin_dep->add_plugin_dependency($plugin_dep);
 */

/*
 * =============================================================================
 * Create a second module with a module dependency
 * =============================================================================
 */

$mod_with_mod_dep = new JKNModTest($space);

$mod_dep = new JKNModuleDependency([
    'id' => $mod_with_plugin_dep->id(),
    'space_id' => $space->id()
]);

$mod_with_mod_dep->add_module_dependency($mod_dep);
