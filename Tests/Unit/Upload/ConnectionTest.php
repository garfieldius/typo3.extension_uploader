<?php
namespace T3x\ExtensionUploader\Tests\Unit\Upload;
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

use T3x\ExtensionUploader\Tests\Unit\ExtensionUploaderTestCase;
use T3x\ExtensionUploader\Upload\Connection;

/**
 * Test the connection utility
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ConnectionTest extends ExtensionUploaderTestCase {
	/**
	 * @var \T3x\ExtensionUploader\Upload\Connection
	 */
	protected $object;

	protected function setUp() {
		$proxy = $this->buildAccessibleProxy('T3x\ExtensionUploader\Upload\Connection');
		$log = $this->getMockBuilder('TYPO3\CMS\Core\Log\Logger')->disableOriginalConstructor()->getMock();
		$this->object = new $proxy();
		$this->object->_set('log', $log);
	}

	public function testSetClient() {
		$client = new \SoapClient('http://typo3.org/wsdl/tx_ter_wsdl.php');
		$this->object->setClient($client);
		$this->assertEquals($client, $this->object->_get('client'));
	}

	public function testSetPassword() {
		$this->object->setPassword('verySecurePassword');
		$this->assertEquals('verySecurePassword', $this->object->_get('password'));
	}

	public function testSetUsername() {
		$this->object->setUsername('myUser');
		$this->assertEquals('myUser', $this->object->_get('username'));
	}

	public function testSetWsdlUrl() {
		$this->object->setWsdlUrl('myWsdlUrl');
		$this->assertEquals('myWsdlUrl', $this->object->_get('wsdlUrl'));
	}

	public function testUploadExtension() {
		$data = array(
			'Some' => 'dummy',
			'data' => 'here'
		);
		$username = 'myName';
		$password = 'verySecurePassword';

		$authHeader = new \stdClass();
		$authHeader->username = $username;
		$authHeader->password = $password;
		$soapHeader = new \SoapHeader(Connection::HEADER_NAMESPACE, 'HeaderLogin', $authHeader, TRUE);

		$client = $this->getMock('SoapClient', array(), array('http://typo3.org/wsdl/tx_ter_wsdl.php'));
		$client->expects($this->once())->method('__soapCall')->with('uploadExtension', $data, NULL, $soapHeader);

		$this->object->setUsername($username);
		$this->object->setPassword($password);
		$this->object->setClient($client);
		$this->object->uploadExtension($data);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ConnectionException
	 */
	public function testUploadExtensionFailure() {
		$data = array(
			'Some' => 'dummy',
			'data' => 'here'
		);
		$username  = 'myName';
		$password  = 'verySecurePassword';
		$exception = new \SoapFault('DataEncodingUnknown', 'Just testing');

		$authHeader = new \stdClass();
		$authHeader->username = $username;
		$authHeader->password = $password;
		$soapHeader = new \SoapHeader(Connection::HEADER_NAMESPACE, 'HeaderLogin', $authHeader, TRUE);

		$client = $this->getMock('SoapClient', array(), array('http://typo3.org/wsdl/tx_ter_wsdl.php'));
		$client->expects($this->once())->method('__soapCall')->with('uploadExtension', $data, NULL, $soapHeader)->will($this->throwException($exception));

		$this->object->setUsername($username);
		$this->object->setPassword($password);
		$this->object->setClient($client);
		$this->object->uploadExtension($data);
	}

	/**
	 *
	 * @covers \T3x\ExtensionUploader\Upload\Connection::connectClient
	 */
	public function testconnectClient() {
		$this->object->setWsdlUrl('http://typo3.org/wsdl/tx_ter_wsdl.php');
		$this->object->setUsername('myUser');
		$this->object->setPassword('verySecurePassword');
		$client = $this->object->connectClient();
		$this->assertTrue($client instanceof \SoapClient);
	}

	/**
	 *
	 * @expectedException \T3x\ExtensionUploader\Upload\ConnectionException
	 */
	public function testConectClientThrowsExceptionIfAMalformedUrlIsProvided() {
		$this->object->setWsdlUrl('not\\reachable');
		$this->object->setUsername('myUser');
		$this->object->setPassword('verySecurePassword');
		$this->object->connectClient();
	}

	/**
	 *
	 * @expectedException \T3x\ExtensionUploader\Upload\ConnectionException
	 */
	public function testConectClientCatchesSegfaultAndThrowsInternalException() {
		$this->object->setWsdlUrl('http://typo3.org/no-wsdl-here');
		$this->object->setUsername('myUser');
		$this->object->setPassword('verySecurePassword');
		$this->object->connectClient();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ConnectionException
	 */
	public function testRequestCallThrowsExceptionIfNoClientIsSet() {
		$this->object->uploadExtension(array());
	}

	public function testReactIdIsUsedOnSecondCallIfItHasBeenProvidedInTheFirstCallsResponse() {

		$token = md5(uniqid());
		$username = 'myUser';
		$password = 'verySecurePassword';

		$header1 = new \stdClass();
		$header1->username = $username;
		$header1->password = $password;
		$header1 = new \SoapHeader(Connection::HEADER_NAMESPACE, 'HeaderLogin', $header1, TRUE);

		$header2 = new \stdClass();
		$header2->reactid = $token;
		$header2 = new \SoapHeader(Connection::HEADER_NAMESPACE, 'HeaderAuthenticate', $header2, TRUE);

		$client = $this->getMock('SoapClient', array(), array(), '', FALSE);
		$client
			->expects($this->at(0))
			->method('__soapCall')
			->with('uploadExtension', array(), NULL, $header1);
		$client
			->expects($this->at(1))
			->method('__soapCall')
			->with('uploadExtension', array(), NULL, $header2);

		$headerIn = new \stdClass();
		$headerIn->reactid = $token;
		$client->headersIn = array(
			'HeaderAuthenticate' => $headerIn
		);

		$this->object->setClient($client);
		$this->object->setUsername($username);
		$this->object->setPassword($password);
		$this->object->uploadExtension(array());
		$this->object->uploadExtension(array());
	}
}
