<?php

namespace OCA\Migrate_Dav\Command;

use OCA\DAV\CardDAV\AddressBook;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\Connector\Sabre\Principal;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use Sabre\CardDAV\Plugin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateAddressbooks extends Command {

	/** @var IUserManager */
	protected $userManager;

	/** @var \OCA\Migrate_Dav\Service\MigrateAddressbooks  */
	private $service;

	/**
	 * @param IUserManager $userManager
	 * @param IDBConnection $dbConnection
	 * @param IConfig $config
	 * @param ILogger $logger
	 */
	function __construct(IUserManager $userManager,
						 \OCA\Migrate_Dav\Service\MigrateAddressbooks $service
	) {
		parent::__construct();
		$this->userManager = $userManager;
		$this->service = $service;
	}

	protected function configure() {
		$this
				->setName('migrate:addressbooks')
				->setDescription('Migrate addressbooks from the contacts app to core')
				->addArgument('user',
						InputArgument::OPTIONAL,
						'User for whom all addressbooks will be migrated');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->service->setup();

		if ($input->hasArgument('user')) {
			$user = $input->getArgument('user');
			if (!$this->userManager->userExists($user)) {
				throw new \InvalidArgumentException("User <$user> in unknown.");
			}
			$this->service->migrateForUser($user);
		}
		$this->userManager->callForAllUsers(function($user) {
			/** @var IUser $user */
			$this->service->migrateForUser($user->getUID());
		});
	}
}
