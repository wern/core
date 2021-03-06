<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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

namespace OCA\DAV\Tests\Unit\Connector\Sabre;

use OCP\IRequest;
use OCP\IUser;
use Test\TestCase;
use OCP\ISession;
use OCP\IUserSession;

/**
 * Class Auth
 *
 * @package OCA\DAV\Connector\Sabre
 * @group DB
 */
class Auth extends TestCase {
	/** @var ISession */
	private $session;
	/** @var \OCA\DAV\Connector\Sabre\Auth */
	private $auth;
	/** @var IUserSession */
	private $userSession;
	/** @var IRequest */
	private $request;

	public function setUp() {
		parent::setUp();
		$this->session = $this->getMockBuilder('\OCP\ISession')
			->disableOriginalConstructor()->getMock();
		$this->userSession = $this->getMockBuilder('\OCP\IUserSession')
			->disableOriginalConstructor()->getMock();
		$this->request = $this->getMockBuilder('\OCP\IRequest')
			->disableOriginalConstructor()->getMock();
		$this->auth = new \OCA\DAV\Connector\Sabre\Auth(
			$this->session,
			$this->userSession,
			$this->request
		);
	}

	public function testIsDavAuthenticatedWithoutDavSession() {
		$this->session
			->expects($this->once())
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue(null));

		$this->assertFalse($this->invokePrivate($this->auth, 'isDavAuthenticated', ['MyTestUser']));
	}

	public function testIsDavAuthenticatedWithWrongDavSession() {
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('AnotherUser'));

		$this->assertFalse($this->invokePrivate($this->auth, 'isDavAuthenticated', ['MyTestUser']));
	}

	public function testIsDavAuthenticatedWithCorrectDavSession() {
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('MyTestUser'));

		$this->assertTrue($this->invokePrivate($this->auth, 'isDavAuthenticated', ['MyTestUser']));
	}

	public function testValidateUserPassOfAlreadyDAVAuthenticatedUser() {
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->exactly(2))
			->method('getUID')
			->will($this->returnValue('MyTestUser'));
		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->userSession
			->expects($this->exactly(2))
			->method('getUser')
			->will($this->returnValue($user));
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('MyTestUser'));
		$this->session
			->expects($this->once())
			->method('close');

		$this->assertTrue($this->invokePrivate($this->auth, 'validateUserPass', ['MyTestUser', 'MyTestPassword']));
	}

	public function testValidateUserPassOfInvalidDAVAuthenticatedUser() {
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->once())
			->method('getUID')
			->will($this->returnValue('MyTestUser'));
		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->userSession
			->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('AnotherUser'));
		$this->session
			->expects($this->once())
			->method('close');

		$this->assertFalse($this->invokePrivate($this->auth, 'validateUserPass', ['MyTestUser', 'MyTestPassword']));
	}

	public function testValidateUserPassOfInvalidDAVAuthenticatedUserWithValidPassword() {
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->exactly(3))
			->method('getUID')
			->will($this->returnValue('MyTestUser'));
		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->userSession
			->expects($this->exactly(3))
			->method('getUser')
			->will($this->returnValue($user));
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('AnotherUser'));
		$this->userSession
			->expects($this->once())
			->method('login')
			->with('MyTestUser', 'MyTestPassword')
			->will($this->returnValue(true));
		$this->session
			->expects($this->once())
			->method('set')
			->with('AUTHENTICATED_TO_DAV_BACKEND', 'MyTestUser');
		$this->session
			->expects($this->once())
			->method('close');

		$this->assertTrue($this->invokePrivate($this->auth, 'validateUserPass', ['MyTestUser', 'MyTestPassword']));
	}

	public function testValidateUserPassWithInvalidPassword() {
		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(false));
		$this->userSession
			->expects($this->once())
			->method('login')
			->with('MyTestUser', 'MyTestPassword')
			->will($this->returnValue(false));
		$this->session
			->expects($this->once())
			->method('close');

		$this->assertFalse($this->invokePrivate($this->auth, 'validateUserPass', ['MyTestUser', 'MyTestPassword']));
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\NotAuthenticated
	 * @expectedExceptionMessage CSRF check not passed.
	 */
	public function testAuthenticateAlreadyLoggedInWithoutCsrfTokenForNonGet() {
		$request = $this->getMockBuilder('Sabre\HTTP\RequestInterface')
				->disableOriginalConstructor()
				->getMock();
		$response = $this->getMockBuilder('Sabre\HTTP\ResponseInterface')
				->disableOriginalConstructor()
				->getMock();
		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->session
			->expects($this->once())
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue(null));
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->once())
			->method('getUID')
			->will($this->returnValue('MyWrongDavUser'));
		$this->userSession
			->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));

		$response = $this->auth->check($request, $response);
		$this->assertEquals([true, 'principals/users/MyWrongDavUser'], $response);
	}

	public function testAuthenticateAlreadyLoggedInWithoutCsrfTokenForGet() {
		$request = $this->getMockBuilder('Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		$response = $this->getMockBuilder('Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$this->userSession
			->expects($this->exactly(2))
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->session
			->expects($this->once())
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue(null));
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->once())
			->method('getUID')
			->will($this->returnValue('MyWrongDavUser'));
		$this->userSession
			->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->request
			->expects($this->once())
			->method('getMethod')
			->willReturn('GET');

		$response = $this->auth->check($request, $response);
		$this->assertEquals([true, 'principals/users/MyWrongDavUser'], $response);
	}


	public function testAuthenticateAlreadyLoggedInWithCsrfTokenForGet() {
		$request = $this->getMockBuilder('Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		$response = $this->getMockBuilder('Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$this->userSession
			->expects($this->exactly(2))
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->session
			->expects($this->exactly(2))
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue(null));
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->exactly(2))
			->method('getUID')
			->will($this->returnValue('MyWrongDavUser'));
		$this->userSession
			->expects($this->exactly(2))
			->method('getUser')
			->will($this->returnValue($user));
		$this->request
			->expects($this->once())
			->method('passesCSRFCheck')
			->willReturn(true);

		$response = $this->auth->check($request, $response);
		$this->assertEquals([true, 'principals/users/MyWrongDavUser'], $response);
	}

	public function testAuthenticateNoBasicAuthenticateHeadersProvided() {
		$server = $this->getMockBuilder('\Sabre\DAV\Server')
			->disableOriginalConstructor()
			->getMock();
		$server->httpRequest = $this->getMockBuilder('\Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		$server->httpResponse = $this->getMockBuilder('\Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$response = $this->auth->check($server->httpRequest, $server->httpResponse);
		$this->assertEquals([false, 'No \'Authorization: Basic\' header found. Either the client didn\'t send one, or the server is mis-configured'], $response);
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\NotAuthenticated
	 * @expectedExceptionMessage Cannot authenticate over ajax calls
	 */
	public function testAuthenticateNoBasicAuthenticateHeadersProvidedWithAjax() {
		/** @var \Sabre\HTTP\RequestInterface $httpRequest */
		$httpRequest = $this->getMockBuilder('\Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		/** @var \Sabre\HTTP\ResponseInterface $httpResponse */
		$httpResponse = $this->getMockBuilder('\Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$this->userSession
			->expects($this->any())
			->method('isLoggedIn')
			->will($this->returnValue(false));
		$httpRequest
			->expects($this->once())
			->method('getHeader')
			->with('X-Requested-With')
			->will($this->returnValue('XMLHttpRequest'));
		$this->auth->check($httpRequest, $httpResponse);
	}

	public function testAuthenticateNoBasicAuthenticateHeadersProvidedWithAjaxButUserIsStillLoggedIn() {
		/** @var \Sabre\HTTP\RequestInterface $httpRequest */
		$httpRequest = $this->getMockBuilder('\Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		/** @var \Sabre\HTTP\ResponseInterface $httpResponse */
		$httpResponse = $this->getMockBuilder('\Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		/** @var IUser */
		$user = $this->getMock('OCP\IUser');
		$user->method('getUID')->willReturn('MyTestUser');
		$this->userSession
			->expects($this->any())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$this->userSession
			->expects($this->any())
			->method('getUser')
			->willReturn($user);
		$this->session
			->expects($this->atLeastOnce())
			->method('get')
			->with('AUTHENTICATED_TO_DAV_BACKEND')
			->will($this->returnValue('MyTestUser'));
		$httpRequest
			->expects($this->atLeastOnce())
			->method('getHeader')
			->with('Authorization')
			->will($this->returnValue(null));
		$this->assertEquals(
			[true, 'principals/users/MyTestUser'],
			$this->auth->check($httpRequest, $httpResponse)
		);
	}

	public function testAuthenticateValidCredentials() {
		$server = $this->getMockBuilder('\Sabre\DAV\Server')
			->disableOriginalConstructor()
			->getMock();
		$server->httpRequest = $this->getMockBuilder('\Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		$server->httpRequest
			->expects($this->at(0))
			->method('getHeader')
			->with('X-Requested-With')
			->will($this->returnValue(null));
		$server->httpRequest
			->expects($this->at(1))
			->method('getHeader')
			->with('Authorization')
			->will($this->returnValue('basic dXNlcm5hbWU6cGFzc3dvcmQ='));
		$server->httpResponse = $this->getMockBuilder('\Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$this->userSession
			->expects($this->once())
			->method('login')
			->with('username', 'password')
			->will($this->returnValue(true));
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->exactly(2))
			->method('getUID')
			->will($this->returnValue('MyTestUser'));
		$this->userSession
			->expects($this->exactly(2))
			->method('getUser')
			->will($this->returnValue($user));
		$response = $this->auth->check($server->httpRequest, $server->httpResponse);
		$this->assertEquals([true, 'principals/users/username'], $response);
	}

	public function testAuthenticateInvalidCredentials() {
		$server = $this->getMockBuilder('\Sabre\DAV\Server')
			->disableOriginalConstructor()
			->getMock();
		$server->httpRequest = $this->getMockBuilder('\Sabre\HTTP\RequestInterface')
			->disableOriginalConstructor()
			->getMock();
		$server->httpRequest
			->expects($this->at(0))
			->method('getHeader')
			->with('X-Requested-With')
			->will($this->returnValue(null));
		$server->httpRequest
			->expects($this->at(1))
			->method('getHeader')
			->with('Authorization')
			->will($this->returnValue('basic dXNlcm5hbWU6cGFzc3dvcmQ='));
		$server->httpResponse = $this->getMockBuilder('\Sabre\HTTP\ResponseInterface')
			->disableOriginalConstructor()
			->getMock();
		$this->userSession
			->expects($this->once())
			->method('login')
			->with('username', 'password')
			->will($this->returnValue(false));
		$response = $this->auth->check($server->httpRequest, $server->httpResponse);
		$this->assertEquals([false, 'Username or password was incorrect'], $response);
	}
}
