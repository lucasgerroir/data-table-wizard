<?php
/*
   Plugin Name: Data Table Wizard 
   Version: 1.0
   Author: Lucas Gerroir
   Description: Creates a wizard that sets up shortcodes for gravity forms and WP jQuery Data Tables. 
   Text Domain: data_table_wizard
   License: GPLv3
  */


function data_table_wizard() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('data_table_wizard_plugin', false, $pluginDir . '/languages/');
}

//////////////////////////////////
// Run initialization
/////////////////////////////////

if ( !defined('ABSPATH') )
	die('-1');

	define( 'DTW_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'DTW_BASENAME', plugin_basename(__FILE__) );
	define( 'DTW_DIR_URL',  plugins_url( ''  , DTW_BASENAME ) );

	// Initialize i18n
	add_action('plugins_loadedi','data_table_wizard');

	// Run the version check.
	// If it is successful, continue with initialization for this plugin
	// Only load and run the init function if we know PHP version can parse it
    include_once('data_table_wizard_init.php');
    data_table_wizard_init(__FILE__);
