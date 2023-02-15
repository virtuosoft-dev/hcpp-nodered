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

// Custom install page
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

    // Enforce username and password, remove PHP version
    $content .= '
    <script>
        $(function() {
            let borderColor = $("#webapp_nodered_username").css("border-color");
            let toolbar = $(".l-center.edit").html();
            $("label[for=\"webapp_php_version\"]").parent().hide();
            function nr_validate() {
                if ( $("#webapp_nodered_username").val().trim() == "" || $("#webapp_nodered_password").val().trim() == "" ) {
                    $(".l-unit-toolbar__buttonstrip.float-right a").css("opacity", "0.5").css("cursor", "not-allowed");
                    if ($("#webapp_nodered_username").val().trim() == "") {
                        $("#webapp_nodered_username").css("border-color", "red");
                    }else{
                        $("#webapp_nodered_username").css("border-color", borderColor);
                    }
                    if ($("#webapp_nodered_password").val().trim() == "") {
                        $("#webapp_nodered_password").css("border-color", "red");
                    }else{
                        $("#webapp_nodered_password").css("border-color", borderColor);
                    }
                    return false;
                }else{
                    $(".l-unit-toolbar__buttonstrip.float-right a").css("opacity", "1").css("cursor", "");
                    $("#webapp_nodered_username").css("border-color", borderColor);
                    $("#webapp_nodered_password").css("border-color", borderColor);
                    return true;
                }
            };

            // Override the form submition
            $(".l-unit-toolbar__buttonstrip.float-right a").removeAttr("data-action").removeAttr("data-id").click(function() {
                if ( nr_validate() ) {
                    $(".l-sort.clearfix").html("<div class=\"l-unit-toolbar__buttonstrip\"></div><div class=\"l-unit-toolbar__buttonstrip float-right\"><div><div class=\"timer-container\" style=\"float:right;\"><div class=\"timer-button spinner\"><div class=\"spinner-inner\"></div><div class=\"spinner-mask\"></div> <div class=\"spinner-mask-two\"></div></div></div></div></div>");
                    $("#vstobjects").submit();
                }
            });
            $("#vstobjects").submit(function(e) {
                if ( !nr_validate() ) {
                    e.preventDefault();
                }
            });
            $("#webapp_nodered_username").blur(nr_validate).keyup(nr_validate);
            $("#webapp_nodered_password").blur(nr_validate).keyup(nr_validate);
            $(".generate").click(function() {
                setTimeout(function() {
                    nr_validate();
                }, 500)
            });
            nr_validate();
        });
    </script>
    ';
    return $content;
});
