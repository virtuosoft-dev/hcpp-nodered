<?php

namespace Hestia\WebApp\Installers\NodeRED;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;
use function Hestiacp\quoteshellarg\quoteshellarg;

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
		parent::install($options);
		parent::setup($options);
		return true;
	}
}
