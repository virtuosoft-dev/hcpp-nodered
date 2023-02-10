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

// // Look for nodered.config.js to run instead of app.config.js
// $hcpp->add_action( 'start_nodeapp_services', function( $args ) {
//     $nodeapp = str_replace( 'public_html', 'nodeapp', $args[4] );
//     $cmd = $args[5];
//     if ( file_exists( $nodeapp . '/nodered.config.js' ) ) {
//         $cmd = str_replace( 'app.config.js', 'nodered.config.js', $cmd );
//         $args[5] = $cmd;

//         global $hcpp;
//         $hcpp->log( 'NodeRED: Starting nodered.config.js instead of app.config.js' );

//         // TODO: check settings.js for subfolder installation and write to
//         // %home%/%user%/conf/web/%domain%/nginx.ssl.conf_nodered_subfolder.conf
//         // and // %home%/%user%/conf/web/%domain%/nginx.conf_nodered_subfolder.conf
//         // otherwise, erase them.
//     }
//     return $args;
// }, 20);

// // Look for nodered.config.js to shutdown instead of app.config.js
// $hcpp->add_action( 'shutdown_nodeapp_services', function( $args ) {
//     $user = $args[0];
//     $domain = $args[1];
//     $nodeapp = "/home/$user/web/$domain/nodeapp";
//     $cmd = $args[3];
//     if ( file_exists( $nodeapp . '/nodered.config.js' ) ) {
//         $cmd = str_replace( 'app.config.js', 'nodered.config.js', $cmd );
//         $args[3] = $cmd;

//         global $hcpp;
//         $hcpp->log( 'NodeRED: Shutting down nodered.config.js instead of app.config.js' );
//     }
//     return $args;
// }, 20);
