<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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

namespace OC\Connector\Sabre;

use Sabre\DAV\Locks;

class NullLockBackend implements \Sabre\DAV\Locks\Backend\BackendInterface {

	/**
	 * Returns a list of Sabre\DAV\Locks\LockInfo objects
	 *
	 * This method should return all the locks for a particular uri, including
	 * locks that might be set on a parent uri.
	 *
	 * If returnChildLocks is set to true, this method should also look for
	 * any locks in the subtree of the uri for locks.
	 *
	 * @param string $uri
	 * @param bool $returnChildLocks
	 * @return array
	 */
	function getLocks($uri, $returnChildLocks) {
		return [];
	}

	/**
	 * Locks a uri
	 *
	 * @param string $uri
	 * @param Locks\LockInfo $lockInfo
	 * @return bool
	 */
	function lock($uri, Locks\LockInfo $lockInfo) {
		return true;
	}

	/**
	 * Removes a lock from a uri
	 *
	 * @param string $uri
	 * @param Locks\LockInfo $lockInfo
	 * @return bool
	 */
	function unlock($uri, Locks\LockInfo $lockInfo) {
		return true;
	}
}
