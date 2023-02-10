<?php

namespace Hestia\WebApp\Installers\NodeRED;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NodeREDSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Node-RED",
		"group" => "framework",
		"enabled" => true,
		"version" => "3.0.2",
		"thumbnail" => "nr-thumb.png",
	];

	protected $appname = "Node-RED";

	protected $config = [
		"form" => [],
		"database" => false,
		"resources" => [],
		"server" => [
			"nginx" => [],
			"php" => [],
		],
	];

	public function install( array $options = null ): bool {
		parent::install( $options );
        return true;
	}
}
