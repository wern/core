<?php

use OCA\Migrate_Dav\Command\MigrateAddressbooks;

$app = new \OCA\Migrate_Dav\AppInfo\Application();

$userManager = OC::$server->getUserManager();
$service = $app->getMigrationService();

/** @var Symfony\Component\Console\Application $application */
$application->add(new MigrateAddressbooks($userManager, $service));
