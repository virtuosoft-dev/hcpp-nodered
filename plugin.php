<?php
/**
 * Plugin Name: NodeRED
 * Plugin URI: https://github.com/virtuosoft-dev/hcpp-nodered
 * Description: Host and maintain updated Node-RED websites.
 */

// Register the install and uninstall scripts
global $hcpp;
require_once( dirname(__FILE__) . '/nodered.php' );

$hcpp->register_install_script( dirname(__FILE__) . '/install' );
$hcpp->register_uninstall_script( dirname(__FILE__) . '/uninstall' );
