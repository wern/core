<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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


namespace OCA\Federation\BackgroundJob;

use GuzzleHttp\Exception\ClientException;
use OC\BackgroundJob\JobList;
use OC\BackgroundJob\QueuedJob;
use OCA\Federation\DbHandler;
use OCA\Federation\TrustedServers;
use OCP\AppFramework\Http;
use OCP\BackgroundJob\IJobList;
use OCP\Http\Client\IClient;
use OCP\ILogger;
use OCP\IURLGenerator;

/**
 * Class GetSharedSecret
 *
 * request shared secret from remote ownCloud
 *
 * @package OCA\Federation\Backgroundjob
 */
class GetSharedSecret extends QueuedJob{

	/** @var IClient */
	private $httpClient;

	/** @var IJobList */
	private $jobList;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var TrustedServers  */
	private $trustedServers;

	/** @var DbHandler */
	private $dbHandler;

	/** @var ILogger */
	private $logger;

	private $endPoint = '/ocs/v2.php/apps/federation/api/v1/shared-secret?format=json';

	/**
	 * RequestSharedSecret constructor.
	 *
	 * @param IClient $httpClient
	 * @param IURLGenerator $urlGenerator
	 * @param IJobList $jobList
	 * @param TrustedServers $trustedServers
	 * @param ILogger $logger
	 * @param DbHandler $dbHandler
	 */
	public function __construct(
		IClient $httpClient = null,
		IURLGenerator $urlGenerator = null,
		IJobList $jobList = null,
		TrustedServers $trustedServers = null,
		ILogger $logger = null,
		dbHandler $dbHandler = null
	) {
		$this->logger = $logger ? $logger : \OC::$server->getLogger();
		$this->httpClient = $httpClient ? $httpClient : \OC::$server->getHTTPClientService()->newClient();
		$this->jobList = $jobList ? $jobList : \OC::$server->getJobList();
		$this->urlGenerator = $urlGenerator ? $urlGenerator : \OC::$server->getURLGenerator();
		$this->dbHandler = $dbHandler ? $dbHandler : new DbHandler(\OC::$server->getDatabaseConnection(), \OC::$server->getL10N('federation'));
		if ($trustedServers) {
			$this->trustedServers = $trustedServers;
		} else {
			$this->trustedServers = new TrustedServers(
					$this->dbHandler,
					\OC::$server->getHTTPClientService(),
					$this->logger,
					$this->jobList,
					\OC::$server->getSecureRandom(),
					\OC::$server->getConfig()
			);
		}
	}

	/**
	 * run the job, then remove it from the joblist
	 *
	 * @param JobList $jobList
	 * @param ILogger $logger
	 */
	public function execute($jobList, ILogger $logger = null) {
		$jobList->remove($this, $this->argument);
		$target = $this->argument['url'];
		// only execute if target is still in the list of trusted domains
		if ($this->trustedServers->isTrustedServer($target)) {
			$this->parentExecute($jobList, $logger);
		}
	}

	/**
	 * call execute() method of parent
	 *
	 * @param JobList $jobList
	 * @param ILogger $logger
	 */
	protected function parentExecute($jobList, $logger) {
		parent::execute($jobList, $logger);
	}

	protected function run($argument) {
		$target = $argument['url'];
		$source = $this->urlGenerator->getAbsoluteURL('/');
		$source = rtrim($source, '/');
		$token = $argument['token'];

		$this->logger->error("I'm $source and ask $target for the shared secret with the Token $token.", ['app' => 'federation']);

		try {
			$result = $this->httpClient->get(
				$target . $this->endPoint,
				[
					'query' =>
						[
							'url' => $source,
							'token' => $token
						],
					'timeout' => 3,
					'connect_timeout' => 3,
				]
			);

			$status = $result->getStatusCode();

		} catch (ClientException $e) {
			$status = $e->getCode();
			if ($status === Http::STATUS_FORBIDDEN) {
				$this->logger->error($target . ' refused to exchange a shared secret with you.', ['app' => 'federation']);
			} else {
				$this->logger->error($source . ': UNKNOWN CLIENT EXCEPTION', ['app' => 'federation']);
				$this->logger->logException($e, ['app' => 'federation']);
			}
		} catch (\Exception $e) {
			$this->logger->error($source . ': UNKNOWN GENERAL EXCEPTION', ['app' => 'federation']);
			$status = HTTP::STATUS_INTERNAL_SERVER_ERROR;
			$this->logger->logException($e, ['app' => 'federation']);
		}

		// if we received a unexpected response we try again later
		if (
			$status !== Http::STATUS_OK
			&& $status !== Http::STATUS_FORBIDDEN
		) {
			$this->logger->error("Ask $target for the shared secret later again.", ['app' => 'federation']);
			$this->jobList->add(
				'OCA\Federation\BackgroundJob\GetSharedSecret',
				$argument
			);
		}  else {
			$this->logger->error("Got valid return code from $target ($status) so we can remove the token", ['app' => 'federation']);
			// reset token if we received a valid response
			$this->dbHandler->addToken($target, '');
		}

		if ($status === Http::STATUS_OK) {
			$body = $result->getBody();
			$result = json_decode($body, true);
			if (isset($result['ocs']['data']['sharedSecret'])) {
				$this->logger->error("Add shared secret received from $target", ['app' => 'federation']);
				$this->trustedServers->addSharedSecret(
						$target,
						$result['ocs']['data']['sharedSecret']
				);
			} else {
				$this->logger->error(
						'REMOTE SERVER "' . $target . '"" DOES NOT RETURN A VALID SHARED SECRET',
						['app' => 'federation']
				);
				$this->trustedServers->setServerStatus($target, TrustedServers::STATUS_FAILURE);
			}
		}

	}
}
