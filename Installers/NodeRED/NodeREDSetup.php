<?php

namespace Hestia\WebApp\Installers\NodeRED;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;
require_once( '/usr/local/hestia/web/pluginable.php' );

class NodeREDSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "NodeRED",
		"group" => "framework",
		"enabled" => true,
		"version" => "3.0.2",
		"thumbnail" => "nr-thumb.png",
	];

	protected $appname = "nodered";
	protected $config = [
		"form" => [
			"nodered_username" => ["value" => "nradmin"],
			"nodered_password" => "password",
			"install_directory" => ["type" => "text", "value" => "", "placeholder" => "/"],
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
		$hcpp->do_action( 'nodered_install', $options );

		return true;
	}
}
