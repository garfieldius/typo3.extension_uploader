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
use T3x\ExtensionUploader\Domain\Model\LocalExtension;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Test for the uploader
 *
 * @package ExtensionUploader
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UploaderTest extends BaseTestCase {

	/**
	 * @var \T3x\ExtensionUploader\Upload\Uploader
	 */
	protected $uploader;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $signalSlots;

	/**
	 * @var LocalExtension
	 */
	protected $extension;

	protected function setUp() {
		$extension = new LocalExtension();
		$extension->setExtensionKey('some_dummy');
		$extension->setTitle('Some Dummy');
		$extension->setSiteRelPath('typo3conf/ext/some_dummy/');
		$extension->setVersion('3.2.1');
		$extension->_setClone(FALSE);
		$this->extension = $extension;

		$signalSlot = $this->getMock('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
		$this->signalSlots = $signalSlot;

		$this->uploader = $this->objectManager->get($this->buildAccessibleProxy('T3x\ExtensionUploader\Upload\Uploader'));
		$this->uploader->setExtension($extension);
		$this->uploader->injectSignals($signalSlot);
	}

	public function testValidateDoesNothingIfDataIsOk() {
		$settings = array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'minor',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		);
		$preSettings = $settings;
		unset($preSettings['password']);
		$postSettings = $preSettings;
		$postSettings['version'] = '3.3.0';

		$this->signalSlots
			 ->expects($this->at(0))
			 ->method('dispatch')
			 ->with(
				'T3x\ExtensionUploader\Upload\Uploader',
				'preValidate',
				array($this->extension, $preSettings)
			);

		$this->signalSlots
			 ->expects($this->at(1))
			 ->method('dispatch')
			 ->with(
				'T3x\ExtensionUploader\Upload\Uploader',
				'postValidate',
				array($this->extension, $postSettings)
			);

		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'minor',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->assertNull($this->uploader->validate());
	}

	public function testValidateRaisesBugfixVersion() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'bugfix',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
		$settings = $this->uploader->_get('settings');
		$this->assertEquals('3.2.2', $settings['version']);
	}

	public function testValidateRaisesMinorVersion() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'minor',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
		$settings = $this->uploader->_get('settings');
		$this->assertEquals('3.3.0', $settings['version']);
	}

	public function testValidateRaisesMajorVersion() {

		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
		$settings = $this->uploader->_get('settings');
		$this->assertEquals('4.0.0', $settings['version']);
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfNoState() {
		$this->uploader->setSettings(array(
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfInvalidState() {
		$this->uploader->setSettings(array(
			'state' => 11,
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfNoReleaseTypeIsSet() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfInvalidReleaseType() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'ordinary',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfInvalidVersionNumberOnCustomReleaseType() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1-preAlpha1',
			'release' => 'custom',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfVersionNumberTooLow() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.1.2',
			'release' => 'custom',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfEmptyCredentials() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'major'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfInvalidUsername() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'My\User',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	/**
	 * @expectedException \T3x\ExtensionUploader\Upload\ValidationFailedException
	 */
	public function testValidateThrowsExceptionIfShortPassword() {
		$this->uploader->setSettings(array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'myuser',
			'password' => 'nosec'
		));
		$this->uploader->validate();
	}


	public function testValidateAcceptsZeroStateAsAlpha() {
		$this->uploader->setSettings(array(
			'state' => 0,
			'version' => '3.2.1',
			'release' => 'minor',
			'username' => 'my_own_user',
			'password' => 'verySecurePassword'
		));
		$this->uploader->validate();
	}

	public function testUpload() {
		$extension = $this->uploader->_get('extension');
		$extension->_setClone(TRUE);

		$dumyRepo = new \TYPO3\CMS\Extensionmanager\Domain\Model\Repository();
		$dumyRepo->_setClone(TRUE);
		$settings = array(
			'state' => 1,
			'version' => '3.2.1',
			'release' => 'major',
			'username' => 'myUser',
			'password' => 'verySecurePassword'
		);
		$extensionData = array(
			'extKey' => 'some_dummy',
			'title' => 'Dummy Ext'
		);
		$fileData = array(
			'md5A' => 'fileA',
			'md5B' => 'fileB',
		);
		$data = array(
			'accountData'   => array(
				'username'  => $settings['username'],
				'password'  => $settings['password'],
			),
			'extensionData' => $extensionData,
			'filesData'     => $fileData
		);

		$dataCollector = $this->getMock('T3x\ExtensionUploader\Upload\ExtensionDataCollector');
		$dataCollector
			->expects($this->once())
			->method('getDataForExtension')
			->with($extension, $settings)
			->will($this->returnValue($extensionData));

		$filesCollector = $this->getMock('T3x\ExtensionUploader\Upload\FilesCollector');
		$filesCollector
			->expects($this->once())
			->method('collectFilesOfExtension')
			->with($extension->getExtensionKey())
			->will($this->returnValue($fileData));

		$connection = $this->getMock('T3x\ExtensionUploader\Upload\Connection');
		$connection
			->expects($this->once())
			->method('uploadExtension')
			->with($data);

		$objects = $this->getMock('T3x\ExtensionUploader\Utility\ObjectUtility');
		$objects
			->expects($this->once())
			->method('getSoapConnectionForRepository')
			->with($dumyRepo, $settings['username'], $settings['password'])
			->will($this->returnValue($connection));

		$objects
			->expects($this->once())
			->method('getFilesCollector')
			->will($this->returnValue($filesCollector));

		$emconf = $this->getMock('T3x\ExtensionUploader\Upload\EmconfAccess');
		$emconf
			->expects($this->once())
			->method('updateEmconfVersion')
			->with($extensionData['extKey'], $settings['version']);

		$this->uploader->setRepository($dumyRepo);
		$this->uploader->setSettings($settings);
		$this->uploader->injectObjects($objects);
		$this->uploader->injectDataCollector($dataCollector);
		$this->uploader->injectEmconfAccess($emconf);
		$this->uploader->upload();
	}
}
