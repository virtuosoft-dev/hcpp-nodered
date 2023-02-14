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

    // Display install information
    $content = '<style>#vstobjects > div > div:nth-child(8), .app-form span.alert{display:none;}</style>' . $content;
    $msg = '<div style="margin-top:-20px;width:75%;"><span>';
    $msg .= 'The Node-RED framework lives inside the "nodeapp" folder (adjacent to "public_html"). ';
    $msg .= 'It can be a standalone instance in the domain root, or in a subfolder using the ';
    $msg .= '<b>Install Directory</b> field below.</span><br><span style="font-style:italic;color:darkorange;">';
    $msg .= 'Files will be overwritten; be sure the specified <span style="font-weight:bold">Install Directory</span> is empty!</span></div><br>';
    $content = str_replace( '<div class="app-form">', '<div class="app-form">' . $msg, $content );

    // Enforce username and password
    $content .= '
    <script>
        $(function() {
            let bc = $("#webapp_nodered_username").css("border-color");
            function nr_validate() {
                if ( $("#webapp_nodered_username").val().trim() == "" || $("#webapp_nodered_password").val().trim() == "" ) {
                    $("a[data-action=submit]").css("opacity", "0.5").css("cursor", "not-allowed");
                    if ($("#webapp_nodered_username").val().trim() == "") {
                        $("#webapp_nodered_username").css("border-color", "red");
                    }else{
                        $("#webapp_nodered_username").css("border-color", bc);
                    }
                    if ($("#webapp_nodered_password").val().trim() == "") {
                        $("#webapp_nodered_password").css("border-color", "red");
                    }else{
                        $("#webapp_nodered_password").css("border-color", bc);
                    }
                    return false;
                }else{
                    $("a[data-action=submit]").css("opacity", "1").css("cursor", "");
                    $("#webapp_nodered_username").css("border-color", bc);
                    $("#webapp_nodered_password").css("border-color", bc);
                    return true;
                }
            };
            $("#webapp_nodered_username").on("change", nr_validate);
            $("#webapp_nodered_password").on("change", nr_validate);
            nr_validate();
        });
    </script>
    ';
    return $content;
});
