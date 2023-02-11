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

/**
 * Install Node-RED for the given user, domain, and location
 */
$hcpp->add_action( 'nodered_install', function( $options ) {
    global $hcpp;
    $hcpp->log( 'NodeRED: Installing NodeRED' );
    $hcpp->log( $options );
    return $options;
});
