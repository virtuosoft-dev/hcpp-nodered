<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\NodeRED;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class NodeREDSetup extends BaseSetup {
	protected array $info = [
		"name" => "NodeRED",
		"group" => "framework",
		"version" => "latest",
		"thumbnail" => "nr-thumb.png",
	];

	protected array $config = [
		"form" => [
			"nodered_folder" => ["type" => "text", "value" => "", "placeholder" => "/", "label" => "Install Directory"],
			"nodeJS_version" => [
				"type" => "select",
				"options" => [
					"v24: LTS Krypton",
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
				// Node-RED doesn't use PHP; list broadly so the app is always installable
				"supported" => ["7.4", "8.0", "8.1", "8.2", "8.3", "8.4", "8.5"],
			],
		],
	];

	// Skip BaseSetup's public_html cleanup and web/backend template changes; nodered_setup configures the proxy
	public function install(InstallationTarget $target, array $options): void {
		global $hcpp;

		$options['user'] = $this->appcontext->user();
		$options['domain'] = $target->domain->domainName;
		$hcpp->run( 'v-invoke-plugin nodered_setup ' . escapeshellarg( json_encode( $options ) ) );
	}

	protected function setupApplication(InstallationTarget $target, array $options): void {
	}
}
