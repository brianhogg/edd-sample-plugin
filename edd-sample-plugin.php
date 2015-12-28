<?php
/*
Plugin Name: Sample Plugin
Plugin URI: http://brianhogg.com/
Description: Illustrates how to include an updater in your plugin for EDD Software Licensing
Author: Pippin Williamson, Brian Hogg
Author URI: http://pippinsplugins.com
Version: 2.0
*/

defined( 'ABSPATH' ) or die( 'No script kittays please!' );

/**
 * Modify these strings to suit your plugin, and rename the class in edd/edd.php
 * so it won't conflict with any other EDD plugins using this sample
 *
 * You'll also need to replace the translation text domain strings
 *
 * This should be defined in your main plugin file or plugin_file changed appropriately
 *
 * @return object
 */
$edd_vars = array(
    // The plugin file, if this array is defined in the plugin
    'plugin_file' => __FILE__,

    // The current version of the plugin.
    // Also need to change in readme.txt and plugin header.
    'version' => '1.0',

    // The main URL of your store for license verification
    'store_url' => 'http://yoursite.com',

    // Your name
    'author' => 'Brian Hogg',

    // The URL to renew or purchase a license
    'purchase_url' => 'http://yoursite.com',

    // The URL of your contact page
    'contact_url' => 'http://yoursite.com/contact',

    // This should match the download name exactly
    'item_name' => 'Sample Plugin',

    // The option names to store the license key and activation status
    'license_key' => 'edd_sample_license_key',
    'license_status' => 'edd_sample_license_status',

    // Option group param for the settings api
    'option_group' => 'edd_sample_license',

	// The plugin settings admin page slug
    'admin_page_slug' => 'edd-sample',

    // If using add_menu_page, this is the parent slug to add a submenu item underneath.
    // Otherwise we'll add our own parent menu item.
    'parent_menu_slug' => '',

    // The translatable title of the plugin
    'plugin_title' => __( 'Sample Plugin', 'edd-sample' ),

    // Title of the settings page with activation key
    'settings_page_title' => __( 'Settings', 'edd-sample' ),

    // If this plugin depends on another plugin to be installed,
    // we can either check that a class exists or plugin is active.
    // Only one is needed.
    'dependent_class_to_check' => '', // name of class to verify...
    'dependent_plugin' => '', // ...or plugin name for is_plugin_active() call
    'dependent_plugin_title' => __( 'Dependent Plugin Name', 'edd-sample' ),
);

if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater
    include( dirname( __FILE__ ) . '/edd/EDD_SL_Plugin_Updater.php' );
}

// You should rename the class in edd/edd.php and modify this to avoid any conflicts
if ( !class_exists( 'EDD_Sample_Plugin' ) )
	require_once( dirname( __FILE__ ) . '/edd/edd.php' );

// Kick off our EDD class
new EDD_Sample_Plugin( $edd_vars );
