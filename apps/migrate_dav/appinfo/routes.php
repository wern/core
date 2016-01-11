<?php
/**
 * Copyright (c) 2016 Thomas Müller
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$app = new \OCA\Migrate_Dav\AppInfo\Application();
$app->registerRoutes($this,
	[
		'routes' => [
			[
				'name' => 'addressbooks#migrate',
				'url' => '/addressbooks/',
				'verb' => 'POST'],
		],
		'resources' => [
		]
	]);
