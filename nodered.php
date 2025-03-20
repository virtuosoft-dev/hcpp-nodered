<?php
/**
 * Extend the HestiaCP Pluginable object with our Node-RED object for
 * allocating NodeRED instances.
 * 
 * @author Virtuosoft/Stephen J. Carnam
 * @license AGPL-3.0, for other licensing options contact support@virtuosoft.com
 * @link https://github.com/virtuosoft-dev/hcpp-nodered
 * 
 */

if ( ! class_exists( 'NodeRED' ) ) {
    class NodeRED extends HCPP_Hooks {
        public $supported = ['18','19','20','21','22'];

        /**
         * Customize Node-RED install screen
         */ 
        public function hcpp_add_webapp_xpath( $xpath ) {
            if ( ! (isset( $_GET['app'] ) && $_GET['app'] == 'NodeRED' ) ) return $xpath;
            global $hcpp;

            // Check for bash shell user
            $user = $_SESSION["user"];
            if ($_SESSION["look"] != "") {
                $user = $_SESSION["look"];
            }
            $domain = $_GET['domain'];
            $domain = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $domain);
            $shell = $hcpp->run( "v-list-user $user json")[$user]['SHELL'];
            if ( $shell != 'bash' ) {
                $style = '<style>div.u-mb10{display:none;}</style>';
                $html = '<span class="u-mb10">Cannot continue. User "' . $user . '" must have bash login ability.</span>';
            }else{
                $style = '<style>#webapp_php_version, label[for="webapp_php_version"]{display:none;}</style>';
                $html =  '<div class="u-mb10">
                              The Node-RED instance lives inside the "nodeapp" folder (next to "public_html"). It can be a
                              standalone instance in the domain root, or in a subfolder using the <b>Install Directory</b> 
                              field above.
                          </div>';
            }
            $xpath = $hcpp->insert_html( $xpath, '//div[contains(@class, "form-container")]', $html );
            $xpath = $hcpp->insert_html( $xpath, '/html/head', $style );

            // Remove existing public_html related alert if present
            $alert_div = $xpath->query('//div[@role="alert"][1]');
            if ( $alert_div->length > 0 ) {
                $alert_div = $alert_div[0];
                $alert_div->parentNode->removeChild( $alert_div );
            }

            // Insert our own alert about non-empty nodeapp folder
            $folder = "/home/$user/web/$domain/nodeapp";
            if ( file_exists( $folder ) && iterator_count(new \FilesystemIterator( $folder, \FilesystemIterator::SKIP_DOTS)) > 0 ) {
                $html = '<div class="alert alert-info u-mb10" role="alert">
                        <i class="fas fa-info"></i>
                        <div>
                            <p class="u-mb10">Data Loss Warning!</p>
                            <p class="u-mb10">Your nodeapp folder already has files uploaded to it. The installer will overwrite your files and/or the installation might fail.</p>
                            <p>Please make sure ~/web/' . $domain . '/nodeapp is empty or an empty subdirectory is specified!</p>
                        </div>
                    </div>';
                $xpath = $hcpp->insert_html( $xpath, '//div[contains(@class, "form-container")]', $html, true );
            }
            return $xpath;
        }

        /**
         * Install, uninstall, or setup Node-RED with the given options
         * This can be invoked from the command line v-invoke-plugin and
         * is used by the webapp installer.
         */
        public function hcpp_invoke_plugin( $args ) {
            if ( count( $args ) < 0 ) return $args;
            global $hcpp;
            
            // Install Node-RED on supported NodeJS versions
            if ( $args[0] == 'nodered_install' ) {

                // Get list of installed and supported NodeJS versions
                $versions = $hcpp->nodeapp->get_versions();
                $majors = [];

                foreach( $versions as $ver ) {
                    $major = $hcpp->getLeftMost( $ver['installed'], '.' );

                    // Check for supported version
                    if ( in_array( $major, $this->supported ) ) {
                        $cmd = 'nvm use ' . $major . ' && echo "~" && npm show node-red version --no-color && ';
                        $cmd .= 'npm list -g node-red --depth=0 --no-color';
                        $parse  = $hcpp->runuser('', $cmd );
                        $latest_pkg = trim( $hcpp->delLeftMost( $parse, '~' ) );
                        $latest_pkg = $hcpp->getLeftMost( $latest_pkg, "\n" );
                        $current_pkg = trim( $hcpp->delLeftMost( $parse . '@', '@' ) );
                        $current_pkg = $hcpp->getLeftMost( $current_pkg, "\n" );


                        // Check if node-red is missing or outdated
                        if ( $current_pkg !== $latest_pkg ) {
                            $majors[] = $major;
                        }
                    }
                }

                // Install Node-RED on supported NodeJS versions
                if ( count( $majors ) > 0 ) {
                    $hcpp->nodeapp->do_maintenance( $majors, function( $stopped ) use( $hcpp, $majors ) {
                        foreach( $majors as $major ) {
                            $cmd = "nvm use $major && ";
                            $cmd .= '(npm list -g node-red || npm install -g --unsafe-perm node-red --no-interactive) ';
                            $cmd .= '&& npm update -g node-red --no-interactive < /dev/null';
                            $hcpp->runuser( '', $cmd );
                        }
                    });
                }
            }
            
            // Uninstall Node-RED on supported NodeJS versions
            if ( $args[0] == 'nodered_uninstall' ) {

                // Get list of installed and supported NodeJS versions
                $versions = $hcpp->nodeapp->get_versions();
                $majors = [];
                foreach( $versions as $ver ) {
                    $major = $hcpp->getLeftMost( $ver['installed'], '.' );
                    if ( in_array( $major, $this->supported ) ) {
                        $majors[] = $major;
                    }
                }

                // Uninstall Node-RED on supported NodeJS versions
                $hcpp->nodeapp->do_maintenance( $this->supported, function( $stopped ) use( $hcpp, $majors ) {
                    foreach( $majors as $major ) {
                        $cmd = "nvm use $major && npm uninstall -g node-red --no-interactive";
                        $hcpp->runuser( '', $cmd );
                    }
                });
            }

            // Setup Node-RED with the supported NodeJS on the given domain 
            if ( $args[0] == 'nodered_setup' ) {
                $options = json_decode( $args[1], true );
                $hcpp->log( $options );
                $user = $options['user'];
                $domain = $options['domain'];
                $nodejs_version = trim( $hcpp->getLeftMost( $options['nodeJS_version'], ':' ), "v \t\n\r\0\x0B" );
                $nodered_folder = $options['nodered_folder'];
                if ( $nodered_folder == '' || $nodered_folder[0] != '/' ) $nodered_folder = '/' . $nodered_folder;
                $nodeapp_folder = "/home/$user/web/$domain/nodeapp";
                
                // Create parent nodeapp folder first this way to avoid CLI permissions issues
                mkdir( $nodeapp_folder, 0755, true );
                chown( $nodeapp_folder, $user );
                chgrp( $nodeapp_folder, $user );
                $nodered_folder = $nodeapp_folder . $nodered_folder;
                $nodered_root = $hcpp->delLeftMost( $nodered_folder, $nodeapp_folder ); 
                $hcpp->runuser( $user, "mkdir -p $nodered_folder" );

                // Copy over nodeapp files
                $hcpp->copy_folder( __DIR__ . '/nodeapp', $nodered_folder, $user );
                chmod( $nodeapp_folder, 0755 );

                // Update the .nvmrc file
                file_put_contents( $nodered_folder . '/.nvmrc', "v$nodejs_version" );

                // Create Node-RED compatible bcrypt hash for password
                $password = $options['nodeRED_password'];
                $hash = password_hash( $password, PASSWORD_BCRYPT, ["code" => 8] );
                $prefix = $hcpp->getLeftMost( $hash, '$2y$' ) . '$2y$';
                $prefix = str_replace( '$2y$', '$2a$', $prefix );
                $hash = $prefix . $hcpp->delLeftMost( $hash, '$2y$' );

                // Generate a random secret key
                $secret_key = $hcpp->random_chars( 32 );            
           
                // Update settings.js with our user options
                $settings = file_get_contents( $nodered_folder . '/settings.js' );
                $settings = str_replace( '%nodeRED_username%', $options['nodeRED_username'], $settings );
                $settings = str_replace( '%nodeRED_password%', $hash, $settings );
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
                    $ext = $hcpp->run( "v-list-web-domain '$user' '$domain' json" )[$domain]['PROXY_EXT'];
                    $ext = str_replace( ' ', ',', $ext );
                    $hcpp->run( "v-change-web-domain-proxy-tpl '$user' '$domain' 'NodeApp' '$ext' 'no'" );
                }else{
                    $hcpp->nodeapp->generate_nginx_files( $nodeapp_folder );
                    $hcpp->nodeapp->startup_apps( $nodeapp_folder );
                }
                $hcpp->run( "v-restart-proxy" );
            }
            return $args;
        }      

    }
    global $hcpp;
    $hcpp->register_plugin( NodeRED::class );
    // class NodeRED {
    //     /**
    //      * Constructor, listen for the invoke and render events
    //      */
    //     public function __construct() {
    //         global $hcpp;
    //         $hcpp->nodered = $this;
    //         $hcpp->add_action( 'hcpp_invoke_plugin', array( $this, 'setup' ) );
    //         $hcpp->add_action( 'hcpp_render_body', array( $this, 'hcpp_render_body' ) );
    //     }

    //     // Install Node-RED based on the given user options
    //     public function setup( $args ) {
    //         if ( $args[0] != 'nodered_install' ) return $args;

    //         global $hcpp;
    //         $options = json_decode( $args[1], true );
        
    //         $user = $options['user'];
    //         $domain = $options['domain'];
    //         $nodered_folder = $options['nodered_folder'];
    //         if ( $nodered_folder == '' || $nodered_folder[0] != '/' ) $nodered_folder = '/' . $nodered_folder;
    //         $nodeapp_folder = "/home/$user/web/$domain/nodeapp";
    //         $nodered_folder = $nodeapp_folder . $nodered_folder;
        
    //         // Create the nodeapp folder 
    //         $cmd = "mkdir -p " . escapeshellarg( $nodered_folder ) . " ; ";
    //         $cmd .= "chown -R $user:$user " . escapeshellarg( $nodeapp_folder );
    //         shell_exec( $cmd );
        
    //         // Copy over nodered files
    //         $hcpp->copy_folder( __DIR__ . '/nodeapp', $nodered_folder, $user );
    //         chmod( $nodeapp_folder, 0751 );
        
    //         // Create Node-RED compatible bcrypt hash for password
    //         $password = $options['nodered_password'];
    //         $hash = password_hash( $password, PASSWORD_BCRYPT, ["code" => 8] );
    //         $prefix = $hcpp->getLeftMost( $hash, '$2y$' ) . '$2y$';
    //         $prefix = str_replace( '$2y$', '$2a$', $prefix );
    //         $hash = $prefix . $hcpp->delLeftMost( $hash, '$2y$' );
        
    //         // Generate a random secret key
    //         $secret_key = $hcpp->nodeapp->random_chars( 32 );
        
    //         // Default editor and http node root to the given folder
    //         $nodered_root = $hcpp->delLeftMost( $nodered_folder, $nodeapp_folder );
        
    //         // Update settings.js with our user options
    //         $settings = file_get_contents( $nodered_folder . '/settings.js' );
    //         $settings = str_replace( '%nodered_username%', $options['nodered_username'], $settings );
    //         $settings = str_replace( '%nodered_password%', $hash, $settings );
    //         $settings = str_replace( '%secret_key%', $secret_key, $settings );
    //         $settings = str_replace( '%nodered_root%', $nodered_root, $settings );
    //         if ( isset( $options['projects'] ) ) {
    //             $settings = str_replace( '%projects%', $options['projects'], $settings );
    //         }else{
    //             $settings = str_replace( '%projects%', 'false', $settings );
    //         }
    //         file_put_contents( $nodered_folder . '/settings.js', $settings );
        
    //         // Cleanup, allocate ports, prepare nginx and start services
    //         $hcpp->nodeapp->shutdown_apps( $nodeapp_folder );
    //         $hcpp->nodeapp->allocate_ports( $nodeapp_folder );
        
    //         // Update proxy and restart nginx
    //         if ( $nodeapp_folder . '/' == $nodered_folder ) {
    //             $hcpp->run( "change-web-domain-proxy-tpl $user $domain NodeApp" );
    //         }else{
    //             $hcpp->nodeapp->generate_nginx_files( $nodeapp_folder );
    //             $hcpp->nodeapp->startup_apps( $nodeapp_folder );
    //             $hcpp->run( "restart-proxy" );
    //         }
    //         return $args;
    //     }

    //     // Custom install page
    //     public function hcpp_render_body( $args ) {
    //         global $hcpp;
    //         if ( $args['page'] !== 'setup_webapp') return $args;
    //         if ( strpos( $_SERVER['REQUEST_URI'], '?app=NodeRED' ) === false ) return $args;
    //         $content = $args['content'];
    //         $user = trim($args['user'], "'");
    //         $shell = $hcpp->run( "list-user $user json")[$user]['SHELL'];
        
    //         // Suppress Data loss alert, and PHP version selector
    //         $content = '<style>.u-mb10:last-of-type,.form-group:last-of-type,.alert.alert-info{display:none;}</style>' . $content;
    //         if ( $shell != 'bash' ) {
        
    //             // Display bash requirement
    //             $content = '<style>.form-group{display:none;}</style>' . $content;
    //             $msg = '<div style="margin-top:-20px;width:75%;"><span>';
    //             $msg .= 'Cannot contiue. User "' . $user . '" must have bash login ability.</span>';
    //             $msg .= '<script>$(function(){$(".l-unit-toolbar__buttonstrip.float-right a").css("display", "none");});</script>';
    //         }elseif ( !is_dir('/usr/local/hestia/plugins/nodeapp') ) {
        
    //             // Display missing nodeapp requirement
    //             $content = '<style>.form-group{display:none;}</style>' . $content;
    //             $msg = '<div style="margin-top:-20px;width:75%;"><span>';
    //             $msg .= 'Cannot contiue. The Node-RED Quick Installer requires the NodeApp plugin.</span>';
    //             $msg .= '<script>$(function(){$(".l-unit-toolbar__buttonstrip.float-right a").css("display", "none");});</script>';
    //         }else{
        
    //             // Display install information
    //             $msg = '<div style="margin-top:-20px;width:75%;"><span>';
    //             $msg .= 'The Node-RED instance lives inside the "nodeapp" folder (adjacent to "public_html"). ';
    //             $msg .= 'It can be a standalone instance in the domain root, or in a subfolder using the ';
    //             $msg .= '<b>Install Directory</b> field below.</span> The specified <b>Install Directory</b> must be non-existent or empty.<br><br>';
                
    //             // Enforce username and password, remove PHP version
    //             $msg .= '
    //             <script>
    //                 document.addEventListener("DOMContentLoaded", function() { 
    //                     let borderColor = $("#webapp_nodered_username").css("border-color");
    //                     let toolbar = $(".l-center.edit").html();
    //                     function nr_validate() {
    //                         if ( $("#webapp_nodered_username").val().trim() == "" || $("#webapp_nodered_password").val().trim() == "" ) {
    //                             $(".l-unit-toolbar__buttonstrip.float-right a").css("opacity", "0.5").css("cursor", "not-allowed");
    //                             if ($("#webapp_nodered_username").val().trim() == "") {
    //                                 $("#webapp_nodered_username").css("border-color", "red");
    //                             }else{
    //                                 $("#webapp_nodered_username").css("border-color", borderColor);
    //                             }
    //                             if ($("#webapp_nodered_password").val().trim() == "") {
    //                                 $("#webapp_nodered_password").css("border-color", "red");
    //                             }else{
    //                                 $("#webapp_nodered_password").css("border-color", borderColor);
    //                             }
    //                             return false;
    //                         }else{
    //                             $(".l-unit-toolbar__buttonstrip.float-right a").css("opacity", "1").css("cursor", "");
    //                             $("#webapp_nodered_username").css("border-color", borderColor);
    //                             $("#webapp_nodered_password").css("border-color", borderColor);
    //                             return true;
    //                         }
    //                     };
        
    //                     // Override the form submition
    //                     $(".l-unit-toolbar__buttonstrip.float-right a").removeAttr("data-action").removeAttr("data-id").click(function() {
    //                         if ( nr_validate() ) {
    //                             $(".l-sort.clearfix").html("<div class=\"l-unit-toolbar__buttonstrip\"></div><div class=\"l-unit-toolbar__buttonstrip float-right\"><div><div class=\"timer-container\" style=\"float:right;\"><div class=\"timer-button spinner\"><div class=\"spinner-inner\"></div><div class=\"spinner-mask\"></div> <div class=\"spinner-mask-two\"></div></div></div></div></div>");
    //                             $("#vstobjects").submit();
    //                         }
    //                     });
    //                     $("#vstobjects").submit(function(e) {
    //                         if ( !nr_validate() ) {
    //                             e.preventDefault();
    //                         }
    //                     });
    //                     $("#webapp_nodered_username").blur(nr_validate).keyup(nr_validate);
    //                     $("#webapp_nodered_password").blur(nr_validate).keyup(nr_validate);
    //                     $(".generate").click(function() {
    //                         setTimeout(function() {
    //                             nr_validate();
    //                         }, 500)
    //                     });
    //                     nr_validate();
    //                 });
    //             </script>
    //             ';
    //         }
    //         if ( strpos( '<div class="app-form">', $content ) !== false ) {
    //             $content = str_replace( '<div class="app-form">', '<div class="app-form">' . $msg, $content ); // Hestia 1.6.X
    //         }else{
    //             $content = str_replace( '<h1 ', $msg . '<h1 style="padding-bottom:0;" ', $content ); // Hestia 1.7.X
    //         }
    //         $args['content'] = $content;
    //         return $args;
    //     }
    // }
    // new NodeRED();
}
