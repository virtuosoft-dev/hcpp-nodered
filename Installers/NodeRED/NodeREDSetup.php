<?php

namespace Hestia\WebApp\Installers\NodeRED;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NodeREDSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "NodeRED",
		"group" => "framework",
		"enabled" => true,
		"version" => "latest",
		"thumbnail" => "nr-thumb.png",
	];

	protected $appname = "nodered";
	protected $config = [
		"form" => [
			"nodered_folder" => ["type" => "text", "value" => "", "placeholder" => "/", "label" => "Install Directory"],
			"nodeJS_version" => [
				"type" => "select",
				"options" => [
					"v18: LTS Hydrogen",
					"v20: LTS Iron",
					"v22: LTS Jod",
				],
			],
			"nodeRED_username" => ["value" => "nradmin"],
			"nodeRED_password" => "password",
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
		$hcpp->run( 'v-invoke-plugin nodered_setup ' . escapeshellarg( json_encode( $options ) ) );
		return true;
	}
}
