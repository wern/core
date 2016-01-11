<?php
/**
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

namespace  OCA\Migrate_Dav\Controller;

use OCA\Migrate_Dav\Service\MigrateAddressbooks;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class AddressbooksController extends Controller {

	public function __construct($appName, IRequest $request, MigrateAddressbooks $service) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}

	public function migrate() {

		$this->service->migrateForUser();

		return new JSONResponse([
			'message' => 'All address books migrated successfully.'
		]);
	}
}
