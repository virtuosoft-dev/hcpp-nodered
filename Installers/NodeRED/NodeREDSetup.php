<?php

namespace Hestia\WebApp\Installers\NodeRED;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;
require_once( '/usr/local/hestia/web/pluginable.php' );

class NodeREDSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "NodeRED",
		"group" => "framework",
		"enabled" => true,
		"version" => "4.0.2",
		"thumbnail" => "nr-thumb.png",
	];

	protected $appname = "nodered";
	protected $config = [
		"form" => [
			"nodered_username" => ["value" => "nradmin"],
			"nodered_password" => "password",
			"nodered_folder" => ["type" => "text", "value" => "", "placeholder" => "/", "label" => "Install Directory"],
			"projects" => ["type" => "boolean", "value" => false, "label" => "Enable Git Projects"],
		],
		"database" => false,
		"resources" => [
		],
		"server" => [
			"nginx" => [],
			"php" => [
				"supported" => ["7.3", "7.4", "8.0", "8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null) {
		global $hcpp;

		$parse = explode( '/', $this->getDocRoot() );
		$options['user'] = $parse[2];
		$options['domain'] = $parse[4];
		$hcpp->run( 'invoke-plugin nodered_install ' . escapeshellarg( json_encode( $options ) ) );
		return true;
	}
}
