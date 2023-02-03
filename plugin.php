<?php
/**
 * Plugin Name: NodeRED
 * Plugin URI: https://github.com/steveorevo/hestiacp-nodered
 * Description: NodeRED is a plugin for HestiaCP that allows you to Quick Install a Node-RED instance.
 */

// Register the install and uninstall scripts
global $hcpp;

$hcpp->register_install_script( dirname(__FILE__) . '/install' );
$hcpp->register_uninstall_script( dirname(__FILE__) . '/uninstall' );

// Look for nodered.config.js app to run instead of app.config.js
$hcpp->add_action( 'start_nodeapp_services', function( $args ) {
    global $hcpp;
    $hcpp->log( $args );
}, 20);