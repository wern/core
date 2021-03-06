<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\DAV;

use OCA\DAV\CalDAV\Schedule\IMipPlugin;
use OCA\DAV\Connector\FedAuth;
use OCA\DAV\Connector\Sabre\Auth;
use OCA\DAV\Connector\Sabre\BlockLegacyClientPlugin;
use OCA\DAV\Files\CustomPropertiesBackend;
use OCP\IRequest;
use OCP\SabrePluginEvent;
use Sabre\DAV\Auth\Plugin;
use Sabre\DAV\IFile;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\HTTP\Util;

class Server {

	/** @var IRequest */
	private $request;

	public function __construct(IRequest $request, $baseUri) {
		$this->request = $request;
		$this->baseUri = $baseUri;
		$logger = \OC::$server->getLogger();
		$mailer = \OC::$server->getMailer();
		$dispatcher = \OC::$server->getEventDispatcher();

		$root = new RootCollection();
		$this->server = new \OCA\DAV\Connector\Sabre\Server($root);

		// Backends
		$authBackend = new Auth(
			\OC::$server->getSession(),
			\OC::$server->getUserSession(),
			\OC::$server->getRequest()
		);

		// Set URL explicitly due to reverse-proxy situations
		$this->server->httpRequest->setUrl($this->request->getRequestUri());
		$this->server->setBaseUri($this->baseUri);

		$this->server->addPlugin(new BlockLegacyClientPlugin(\OC::$server->getConfig()));
		$authPlugin = new Plugin($authBackend, 'ownCloud');
		$this->server->addPlugin($authPlugin);

		// allow setup of additional auth backends
		$event = new SabrePluginEvent($this->server);
		$dispatcher->dispatch('OCA\DAV\Connector\Sabre::authInit', $event);

		$this->server->addPlugin(new \OCA\DAV\Connector\Sabre\DummyGetResponsePlugin());
		$this->server->addPlugin(new \OCA\DAV\Connector\Sabre\ExceptionLoggerPlugin('webdav', $logger));
		$this->server->addPlugin(new \OCA\DAV\Connector\Sabre\LockPlugin());
		$this->server->addPlugin(new \Sabre\DAV\Sync\Plugin());

		// acl
		$acl = new \Sabre\DAVACL\Plugin();
		$acl->defaultUsernamePath = 'principals/users';
		$this->server->addPlugin($acl);

		// calendar plugins
		$this->server->addPlugin(new \Sabre\CalDAV\Plugin());
		$this->server->addPlugin(new \Sabre\CalDAV\ICSExportPlugin());
		$this->server->addPlugin(new \Sabre\CalDAV\Schedule\Plugin());
		$this->server->addPlugin(new IMipPlugin($mailer, $logger));
		$this->server->addPlugin(new \Sabre\CalDAV\Subscriptions\Plugin());
		$this->server->addPlugin(new \Sabre\CalDAV\Notifications\Plugin());
		$this->server->addPlugin(new DAV\Sharing\Plugin($authBackend, \OC::$server->getRequest()));

		// addressbook plugins
		$this->server->addPlugin(new \OCA\DAV\CardDAV\Plugin());

		// system tags plugins
		$this->server->addPlugin(new \OCA\DAV\SystemTag\SystemTagPlugin(\OC::$server->getSystemTagManager()));

		// comments plugin
		$this->server->addPlugin(new \OCA\DAV\Comments\CommentsPlugin(
			\OC::$server->getCommentsManager(),
			\OC::$server->getUserSession()
		));

		// Some WebDAV clients do require Class 2 WebDAV support (locking), since
		// we do not provide locking we emulate it using a fake locking plugin.
		if($request->isUserAgent([
			'/WebDAVFS/',
			'/Microsoft Office OneNote 2013/',
		])) {
			$this->server->addPlugin(new \OCA\DAV\Connector\Sabre\FakeLockerPlugin());
		}

		// Serve all files with an Content-Disposition of type "attachment"
		$this->server->on('beforeMethod', function (RequestInterface $requestInterface, ResponseInterface $responseInterface) {
			if ($requestInterface->getMethod() === 'GET') {
				$path = $requestInterface->getPath();
				if ($this->server->tree->nodeExists($path)) {
					$node = $this->server->tree->getNodeForPath($path);
					if (($node instanceof IFile)) {
						$responseInterface->addHeader('Content-Disposition', 'attachment');
					}
				}
			}
		});

		// wait with registering these until auth is handled and the filesystem is setup
		$this->server->on('beforeMethod', function () {
			// custom properties plugin must be the last one
			$user = \OC::$server->getUserSession()->getUser();
			if (!is_null($user)) {
				$this->server->addPlugin(
					new \Sabre\DAV\PropertyStorage\Plugin(
						new CustomPropertiesBackend(
							$this->server->tree,
							\OC::$server->getDatabaseConnection(),
							\OC::$server->getUserSession()->getUser()
						)
					)
				);
			}
		});
	}

	public function exec() {
		$this->server->exec();
	}
}
