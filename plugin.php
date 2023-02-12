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

// Install Node-RED based on the given user options
$hcpp->add_action( 'nodered_install', function( $options ) {
    global $hcpp;
    $hcpp->log( 'NodeRED: Installing NodeRED' );
    $hcpp->log( $options );
    return $options;
});

// Display a message on the Node-RED install page
$hcpp->add_action( 'render_page_body_WEB_setup_webapp', function( $content ) {
    global $hcpp;
    if ( strpos( $_SERVER['REQUEST_URI'], '/add/webapp/?app=NodeRED&' ) === false ) return $content;
    $content = '<style>#vstobjects > div > div:nth-child(8), .app-form span.alert{display:none;}</style>' . $content;
    $msg = '<div style="margin-top:-20px;width:75%;"><span>';
    $msg .= 'The Node-RED framework lives inside the "nodeapp" folder (adjacent to "public_html"). ';
    $msg .= 'It can be a standalone instance in the domain root, or in a subfolder using the ';
    $msg .= '<b>Install Directory</b> field below.</span><br><span style="font-style:italic;font-weight:bold;';
    $msg .= 'color:darkorange;">Files will be overwritten; be sure the specified Install Directory is empty!</span></div>';
    $content = str_replace( '<div class="app-form">', '<div class="app-form">' . $msg, $content );
    return $content;
});
