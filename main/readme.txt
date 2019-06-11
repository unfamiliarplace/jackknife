================================================================================
JACKKNIFE
=========

Author(s):          Luke Sawczak
Created:            2016
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

Jackknife is the plugin that helps you make plugins.

It's the amateur developer's friend: an OO framework for working with WordPress,
and a set of extendable tools designed to ensure quality and reduce hassle.

The two core features of Jackknife are spaces and tools.

When you build on Jackknife, you register and configure your space. Then, you
create your modules and assign settings pages, then add them to the space.

To create your modules, you make use of the tools, whether they be finished
objects, extendable classes, interfaces, and traits, or just static functions.

Jackknife will handle the rest.

================================================================================
USAGE
=====

-------------
1. Setting up
-------------

To set up your Jackknife extension, you should make two files. One is a main
plugin file, e.g. myplugins/myplugin.php in the WP plugins folder. This one will
simply hook onto the Jackknife hook. This lets you load only if JKN is active:

add_action( 'jkn_register', function() { require_once 'jkn_api.php'; } );

The second file is jkn_api.php, and it's where you'll register your space.

    a. Create the space and set up its menu (icon, order, hide page from user):
            $space = JKNAPI::create_space( 'prefix', 'Name' );
            $space->set_icon_url( 'assets/menu-icon.png' );

    b. Register any dependencies you need:
            $acf = new JKNPluginDependency( [
               'id'     => 'acf_pro',
               'name'   => 'Advanced Custom Fields Pro',
               'url'    => 'https://www.advancedcustomfields.com',
               'file'   => 'advanced-custom-fields-pro/acf.php'
           ] );

    c. Instantiate your modules and add any dependencies:
            $mymod = new MyModule( $space );
            $mymod->add_plugin_dependency( $acf );

    d. Instantiate your settings pages and add them with the desired order:
            $mymod_spage = new MyModuleSettingsPage( $mymod );
            $space->add_settings_page( $mymod_spage, 10 );

Rinse and repeat steps b-d for any other modules.

------------------
2. Writing modules
------------------

-------------------
a. Extend JKNModule
-------------------

The first thing you'll do is extend JKNModule and implement 4 basic functions:

    class MyModule extends JKNModule {
        function id(): string           { return 'mymod'; }
        function name(): string         { return 'My Wonderful Module'; }
        function description(): string  { return 'Adds a custom post type.'; }

        function run_on_startup(): void {
            // Here's where the magic happens
        }
    }

-----------------------------------------
b. Take advantage of the module lifecycle
-----------------------------------------

Modules go through a number of phases in a given request. The one you strictly
need to supply is run_on_startup, but you can take advantage of the others too.

Legend:
    A = Always run the stage
    O = Only run the stage if the module is on
    X = Only run the stage if the module is off
    W = WordPress hook; also only runs if the module is on
    * = Required

A   run_on_load         When you instantiate your module, before plugins_loaded.
                        Use for including any preliminary files.

O   run_on_activation   When the user turns your module on,
                        or Jackknife or your plugin is activated.
                        Use for first-time setup.

O*  run_on_startup      On plugins_loaded, right after JKN itself starts up.
                        Use for main behaviour.

O   run_on_resume       When a dependency becomes met, if the module was paused.
                        Use for resuming behaviour, e.g. rescheduling cron jobs.

X   run_on_pause        When a dependency becomes unmet, if the module was on.
                        Use for pausing behaviour, e.g. descheduling cron jobs.

X   run_on_deactivation When the user turns your module off,
                        or Jackknife or your plugin is deactivated.
                        Use for cleanup, e.g. cache deletion.

X   run_on_uninstall    When the user uninstalls Jackknife or your plugin.
                        Use for last-time cleanup, e.g. option deletion.

W   run_on_init         Runs on WP 'init'.

W   run_on_wp_loaded    Runs on WP 'wp_loaded'.

W   run_on_shutdown     Runs on WP 'wp_shutdown', i.e. when a request finishes.
                        Use for restoring environment variables, e.g. ini_set.

For all stages marked A or X, don't forget that your module has NOT started up
and your dependencies are NOT guaranteed, so just do the prep or clean up!

---------------------------------------------------
c. Use the APIs for Jackknife environment behaviour
---------------------------------------------------

JKNAPI provides a few key access points to your module. For example, it allows
any module code to inspect its own environment. Here are some useful functions:

    JKNAPI::module()    Return your module.
    JKNAPI::mpath()     Return the path to your module's base folder.
    JKNAPI::murl()      Return the URL to your module's base folder.
    JKNAPI::mid()       Return your module ID.

JKNOpts provides ways of working with options. A key component of Jackknife is
proper option qualification in the database, with minimal effort on your part.
In other words, you don't have to worry about prefixes. Here's all you do:

    JKNOpts::get('foo')         Return the value of (qualified) 'foo'.
    JKNOpts::update('foo', 1)   Update the value of (qualified) 'foo'.
    JKNOpts::delete('foo')      Delete the (qualified) 'foo' option.
    JKNOpts::qualify('foo')     Manually qualify 'foo'.

-----------------------------
d. Use the catalogue of tools
-----------------------------

The tools catalogue is an important part of Jackknife. It lets you simplify an
ever-increasing number of tasks, including:

    -- Caching objects to disk
    -- Scheduling future actions via simple cron jobs
    -- Creating custom post types
    -- Making your Advanced Custom Fields field groups programmatic
    -- Minifying and formatting HTML, CSS, and Javascript
    -- Working with files, dates, times, strings, arrays, WP posts, and more

The full catalogue is included in catalogue.txt.

================================================================================
FUTURE
======

There are two main directions in which Jackknife will be developed:

    --  Refining the Jackknife core. Perhaps adding spaces, modules, and
        settings pages can be made even simpler. Perhaps even more work can be
        taken off your hands (e.g. automatic option deletion on uninstall).
            Crazier ideas: A GUI and a public module catalogue/shop. :)

    --  Building the tools catalogue. WordPress is capable of a huge range of
        functionality... and much more of it can be overlaid and simplified
        through Jackknife's OO methodology. :)

        --  As a side note, it might be helpful to rename all core classes
            (those intended be used only when setting up your module, not when
            writing it) with _ (_JKNRegistry, etc.), to make it easier to
            distinguish which are which when using code completion tools.