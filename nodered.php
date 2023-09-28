<?php
/**
 * Extend the HestiaCP Pluginable object with our NodeRED object for
 * allocating NodeRED instances.
 * 
 * @version 1.0.0
 * @license GPL-3.0
 * @link https://github.com/virutosoft-dev/hcpp-nodered
 * 
 */

if ( ! class_exists( 'NodeRED' ) ) {
    class NodeRED {
        /**
         * Constructor, listen for the invoke and render events
         */
        public function __construct() {
            global $hcpp;
            $hcpp->nodered = $this;
            $hcpp->add_action( 'hcpp_invoke_plugin', array( $this, 'setup' ) );
            $hcpp->add_action( 'hcpp_render_body', array( $this, 'hcpp_render_body' ) );
        }

        // Install Node-RED based on the given user options
        public function setup( $args ) {
            if ( $args[0] != 'nodered_install' ) return $args;

            global $hcpp;
            $options = json_decode( $args[1], true );
        
            $user = $options['user'];
            $domain = $options['domain'];
            $nodered_folder = $options['nodered_folder'];
            if ( $nodered_folder == '' || $nodered_folder[0] != '/' ) $nodered_folder = '/' . $nodered_folder;
            $nodeapp_folder = "/home/$user/web/$domain/nodeapp";
            $nodered_folder = $nodeapp_folder . $nodered_folder;
        
            // Create the nodeapp folder 
            $cmd = "mkdir -p " . escapeshellarg( $nodered_folder ) . " && ";
            $cmd .= "chown -R $user:$user " . escapeshellarg( $nodeapp_folder );
            shell_exec( $cmd );
        
            // Copy over nodered files
            $hcpp->copy_folder( __DIR__ . '/nodeapp', $nodered_folder, $user );
        
            // Create Node-RED compatible bcrypt hash for password
            $password = $options['nodered_password'];
            $hash = password_hash( $password, PASSWORD_BCRYPT, ["code" => 8] );
            $prefix = $hcpp->getLeftMost( $hash, '$2y$' ) . '$2y$';
            $prefix = str_replace( '$2y$', '$2a$', $prefix );
            $hash = $prefix . $hcpp->delLeftMost( $hash, '$2y$' );
        
            // Generate a random secret key
            $secret_key = $hcpp->nodeapp->random_chars( 32 );
        
            // Default editor and http node root to the given folder
            $nodered_root = $hcpp->delLeftMost( $nodered_folder, $nodeapp_folder );
        
            // Update settings.js with our user options
            $settings = file_get_contents( $nodered_folder . '/settings.js' );
            $settings = str_replace( '%nodered_username%', $options['nodered_username'], $settings );
            $settings = str_replace( '%nodered_password%', $hash, $settings );
            $settings = str_replace( '%secret_key%', $secret_key, $settings );
            $settings = str_replace( '%nodered_root%', $nodered_root, $settings );
            if ( isset( $options['projects'] ) ) {
                $settings = str_replace( '%projects%', $options['projects'], $settings );
            }else{
                $settings = str_replace( '%projects%', 'false', $settings );
            }
            file_put_contents( $nodered_folder . '/settings.js', $settings );
        
            // Cleanup, allocate ports, prepare nginx and start services
            $hcpp->nodeapp->shutdown_apps( $nodeapp_folder );
            $hcpp->nodeapp->allocate_ports( $nodeapp_folder );
        
            // Update proxy and restart nginx
            if ( $nodeapp_folder . '/' == $nodered_folder ) {
                $hcpp->run( "change-web-domain-proxy-tpl $user $domain NodeApp" );
            }else{
                $hcpp->nodeapp->generate_nginx_files( $nodeapp_folder );
                $hcpp->nodeapp->startup_apps( $nodeapp_folder );
                $hcpp->run( "restart-proxy" );
            }
            return $args;
        }

        // Custom install page
        public function hcpp_render_body( $args ) {
            global $hcpp;
            if ( $args['page'] !== 'setup_webapp') return $args;
            if ( strpos( $_SERVER['REQUEST_URI'], '?app=NodeRED' ) === false ) return $args;
            $content = $args['content'];
            $user = trim($args['user'], "'");
            $shell = $hcpp->run( "list-user $user json")[$user]['SHELL'];
        
            // Suppress Data loss alert, and PHP version selector
            $content = '<style>.u-mb10:last-of-type,.form-group:last-of-type,.alert.alert-info{display:none;}</style>' . $content;
            if ( $shell != 'bash' ) {
        
                // Display bash requirement
                $content = '<style>.form-group{display:none;}</style>' . $content;
                $msg = '<div style="margin-top:-20px;width:75%;"><span>';
                $msg .= 'Cannot contiue. User "' . $user . '" must have bash login ability.</span>';
                $msg .= '<script>$(function(){$(".l-unit-toolbar__buttonstrip.float-right a").css("display", "none");});</script>';
            }elseif ( !is_dir('/usr/local/hestia/plugins/nodeapp') ) {
        
                // Display missing nodeapp requirement
                $content = '<style>.form-group{display:none;}</style>' . $content;
                $msg = '<div style="margin-top:-20px;width:75%;"><span>';
                $msg .= 'Cannot contiue. The Node-RED Quick Installer requires the NodeApp plugin.</span>';
                $msg .= '<script>$(function(){$(".l-unit-toolbar__buttonstrip.float-right a").css("display", "none");});</script>';
            }else{
        
                // Display install information
                $msg = '<div style="margin-top:-20px;width:75%;"><span>';
                $msg .= 'The Node-RED framework lives inside the "nodeapp" folder (adjacent to "public_html"). ';
                $msg .= 'It can be a standalone instance in the domain root, or in a subfolder using the ';
                $msg .= '<b>Install Directory</b> field below.</span> The specified <b>Install Directory</b> must be non-existent or empty.<br><br>';
                
                // Enforce username and password, remove PHP version
                $msg .= '
                <script>
                    document.addEventListener("DOMContentLoaded", function() { 
                        let borderColor = $("#webapp_nodered_username").css("border-color");
                        let toolbar = $(".l-center.edit").html();
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
            }
            if ( strpos( '<div class="app-form">', $content ) !== false ) {
                $content = str_replace( '<div class="app-form">', '<div class="app-form">' . $msg, $content ); // Hestia 1.6.X
            }else{
                $content = str_replace( '<h1 ', $msg . '<h1 style="padding-bottom:0;" ', $content ); // Hestia 1.7.X
            }
            $args['content'] = $content;
            return $args;
        }
    }
    new NodeRED();
}
