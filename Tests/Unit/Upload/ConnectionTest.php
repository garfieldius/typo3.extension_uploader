<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace T3x\ExtensionUploader\Tests\Unit\Upload;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Test the connection utility
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ConnectionTest extends BaseTestCase {
	/**
	 * @var \T3x\ExtensionUploader\Upload\Connection
	 */
	protected $object;

	protected function setUp() {
		$proxy = $this->buildAccessibleProxy('T3x\ExtensionUploader\Upload\Connection');
		$this->object = new $proxy();
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
		$soapHeader = new \SoapHeader('', 'HeaderLogin', $authHeader, TRUE);

		$client = $this->getMock('SoapClient', array(), array('http://typo3.org/wsdl/tx_ter_wsdl.php'));
		$client->expects($this->once())->method('__soapCall')->with('uploadExtension', $data, NULL, $soapHeader);

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
		$exception = new \SoapFault(1, 'Just testing');

		$authHeader = new \stdClass();
		$authHeader->username = $username;
		$authHeader->password = $password;
		$soapHeader = new \SoapHeader('', 'HeaderLogin', $authHeader, TRUE);

		$client = $this->getMock('SoapClient', array(), array('http://typo3.org/wsdl/tx_ter_wsdl.php'));
		$client->expects($this->once())->method('__soapCall')->with('uploadExtension', $data, NULL, $soapHeader)->will($this->throwException($exception));

		$this->object->setClient($client);
		$this->object->uploadExtension($data);
	}

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
}
