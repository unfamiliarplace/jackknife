<?php
/**
 Plugin Name: AAA Test Dep Plugin
 Plugin URI: https://sawczak.ca
 Description: Tests dependencies for Jackknife.
 Author: Luke Sawczak
 Version: 0.000000001
 Author URI: https://sawczak.ca
 */

add_action('admin_notices', function(): void {
    printf('<div class="notice notice-info is-dismissible">'
        . 'The AAA Test Dep Plugin is active.</div>');
});