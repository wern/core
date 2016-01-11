<?php
/**
 * ownCloud - mail
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @copyright Thomas Müller 2014
 */

namespace OCA\Migrate_Dav\AppInfo;

use OCA\Mail\Controller\AccountsController;
use OCA\Mail\Controller\FoldersController;
use OCA\Mail\Controller\MessagesController;
use OCA\Mail\Controller\ProxyController;
use OCA\Mail\Db\MailAccountMapper;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\AutoConfig\AutoConfig;
use OCA\Mail\Service\AutoConfig\ImapConnectivityTester;
use OCA\Mail\Service\AutoConfig\ImapConnector;
use OCA\Mail\Service\AutoConfig\ImapServerDetector;
use OCA\Mail\Service\AutoConfig\MozillaIspDb;
use OCA\Mail\Service\AutoConfig\MxRecord;
use OCA\Mail\Service\AutoConfig\SmtpConnectivityTester;
use OCA\Mail\Service\AutoConfig\SmtpServerDetector;
use OCA\Mail\Service\ContactsIntegration;
use OCA\Mail\Service\Logger;
use OCA\Migrate_Dav\Service\MigrateAddressbooks;
use \OCP\AppFramework\App;
use \OCA\Mail\Controller\PageController;
use OCP\AppFramework\IAppContainer;

class Application extends App {

	public function __construct (array $urlParams=array()) {
		parent::__construct('migrate_dav', $urlParams);

		$this->getContainer()->registerService('OCA\Migrate_Dav\Service\MigrateAddressbooks', function($c) {
			/** @var IAppContainer $c */
			return new MigrateAddressbooks(
				$c->getServer()->getDatabaseConnection(),
				$c->getServer()->getConfig(),
				$c->getServer()->getLogger()
			);
		});
	}

	public function getMigrationService() {
		return $this->getContainer()->query('OCA\Migrate_Dav\Service\MigrateAddressbooks');
	}

}
